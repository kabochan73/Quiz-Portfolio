<?php

namespace App\Services\Grading;

use Anthropic\Client;
use Anthropic\Core\Exceptions\AnthropicException;
use Anthropic\Messages\Tool;
use Anthropic\Messages\ToolChoiceAuto;
use Anthropic\Messages\ToolUseBlock;
use Anthropic\Messages\WebSearchTool20260209;
use App\Enums\GradingLevel;
use App\Models\Question;
use RuntimeException;

/**
 * Claude API(Messages API)を使った本採点の実装。
 *
 * 「問題文と回答のみからAIが自己判断で採点する」(模範解答は与えない)という
 * 要件定義どおりの方針で、選択された全問題を1回のAPIリクエストにまとめて送る。
 * 採点結果はtool use(function calling)で厳密な構造として受け取ることで、
 * 自由文からのパース失敗を避けている。
 *
 * バージョン番号や時事情報のように「時間とともに変わる事実」を含む問題は、
 * AI自身の学習時点の知識が古いと誤採点の原因になる(例: 実際は最新版なのに
 * 「古い情報と食い違う」という理由で不正解にしてしまう)。これを避けるため、
 * Web検索ツールを渡し、必要なら採点前に最新情報を確認できるようにしている。
 */
class ClaudeGradingService implements GradingService
{
    private const MODEL = 'claude-sonnet-5';

    // web_searchを挟むと、稀に検索だけして最後にsubmit_gradesを呼ばずに終わることがある。
    // 外部API起因の一時的なブレなので、諦める前に何回か素直にリトライする。
    private const MAX_ATTEMPTS = 3;

    private readonly Client $client;

    public function __construct()
    {
        $this->client = new Client(
            apiKey: config('services.anthropic.api_key'),
            // Web検索を挟むリクエストは数十秒かかることがあるため、
            // nginx側のfastcgi_read_timeout(300秒)より短い範囲で余裕を持たせる。
            requestOptions: ['timeout' => 180.0],
        );
    }

    public function grade(array $items, GradingLevel $level): array
    {
        $lastError = null;

        for ($attempt = 1; $attempt <= self::MAX_ATTEMPTS; $attempt++) {
            try {
                $message = $this->client->messages->create(
                    maxTokens: 8000,
                    model: self::MODEL,
                    system: $this->systemPrompt($level),
                    messages: [['role' => 'user', 'content' => $this->userPrompt($items)]],
                    tools: [
                        $this->webSearchTool(count($items)),
                        $this->submitGradesTool(),
                    ],
                    // web_search(必要なら)→submit_gradesの順で呼んでほしいので、
                    // ここではsubmit_gradesを強制せず、AIの判断に任せる(auto)。
                    toolChoice: ToolChoiceAuto::with(),
                );
            } catch (AnthropicException $e) {
                // 通信エラー・レートリミット・サーバーエラーなど、外部API起因の一時的な失敗として扱う
                $lastError = $e->getMessage();

                continue;
            }

            // content配列にはweb_searchのtool_useも混ざりうるので、name指定で確実にsubmit_gradesだけを拾う
            $toolUse = collect($message->content)
                ->first(fn ($block) => $block instanceof ToolUseBlock && $block->name === 'submit_grades');

            if (! $toolUse) {
                $lastError = 'Claude APIがsubmit_gradesツールを呼ばずに終了しました。';

                continue;
            }

            try {
                return $this->buildResults($items, $toolUse->input);
            } catch (RuntimeException $e) {
                // 検索を挟むと、稀に問題数と採点結果の件数が食い違うことがある。
                // 一時的なブレとして扱い、諦める前にもう一度試す。
                $lastError = $e->getMessage();
            }
        }

        throw new RuntimeException($lastError ?? 'Claude APIから採点結果が返ってきませんでした。');
    }

    /**
     * submit_gradesツールのinputから、$itemsと同じ順番・件数の採点結果配列を組み立てる。
     * 件数が足りない・indexが噛み合わないなど、AIの出力が不完全な場合はRuntimeExceptionを投げる
     * (呼び出し元でリトライするための合図)。
     */
    private function buildResults(array $items, array $input): array
    {
        $grades = collect($this->extractGrades($input))->keyBy('index');

        return collect($items)
            ->values()
            ->map(function ($item, int $index) use ($grades) {
                $grade = $grades->get($index);

                if (! $grade) {
                    throw new RuntimeException("{$index}番目の問題の採点結果が見つかりませんでした。");
                }

                return [
                    // AIが範囲外の点数を返してもDB制約(0〜100)に収まるよう念のためクランプする
                    'score' => max(0, min(100, (int) $grade['score'])),
                    'feedback' => (string) $grade['feedback'],
                ];
            })
            ->all();
    }

    /**
     * tool_use.inputのgradesを配列として取り出す。
     *
     * 本来は input = {"grades": [ {...}, {...} ]} という構造で返ってくるはずだが、
     * 実際には input.grades がJSON文字列になっていたり、その文字列の中身がさらに
     * {"grades": [...]} と一段ネストしていたりすることがあるため、両方のパターンに対応する。
     */
    private function extractGrades(array $input): array
    {
        $grades = $input['grades'] ?? [];

        if (is_string($grades)) {
            $decoded = json_decode($grades, true) ?? [];
            $grades = $decoded['grades'] ?? $decoded;
        }

        return is_array($grades) ? $grades : [];
    }

    /**
     * 採点レベルに応じて採点方針をプロンプトに明示する(要件定義3.3: レベルごとに方針を固定してブレを抑える)。
     */
    private function systemPrompt(GradingLevel $level): string
    {
        return <<<PROMPT
        あなたは学習アプリの採点者です。ユーザーが自由記述で答えた解答を、それぞれ0〜100点の整数で採点し、
        日本語で簡潔な評価コメント(良い点・改善点)を付けてください。
        模範解答は与えられないので、問題文の内容から妥当な採点基準を自分で判断してください。

        採点の厳しさ: {$level->policyText()}

        ソフトウェアのバージョン番号、時事的な出来事、統計・料金など「時間とともに変わる事実」が
        問題や回答に含まれる場合、あなたの知識は古い可能性があります。自分の記憶だけを根拠に
        「不正解」と断定せず、判断に自信が持てないときはweb_searchツールで現在の情報を確認してから
        採点してください。

        検索が終わったら(検索が不要な問題ではそのまま)、必ずsubmit_gradesツールを使い、
        渡された問題の数だけ採点結果を返してください。
        PROMPT;
    }

    /**
     * 選択された全問題を「【問題0】...【回答0】...」の形でまとめて1つのメッセージにする。
     *
     * @param  array<int, array{question: Question, body: string}>  $items
     */
    private function userPrompt(array $items): string
    {
        return collect($items)
            ->values()
            ->map(fn (array $item, int $index) => sprintf(
                "【問題%d】\n%s\n\n【回答%d】\n%s",
                $index,
                $item['question']->body,
                $index,
                $item['body']
            ))
            ->implode("\n\n---\n\n");
    }

    /**
     * Anthropicがサーバー側で実行してくれるWeb検索ツール。
     * 1回のリクエストで最大何回まで検索してよいかを問題数に応じて決める
     * (無制限にすると採点1回あたりの時間・コストが読めなくなるため)。
     */
    private function webSearchTool(int $questionCount): WebSearchTool20260209
    {
        return WebSearchTool20260209::with(
            maxUses: min(10, max(2, $questionCount * 2)),
        );
    }

    /**
     * 採点結果を厳密な構造で受け取るためのtool定義。
     */
    private function submitGradesTool(): Tool
    {
        return Tool::with(
            name: 'submit_grades',
            description: '各問題の採点結果をまとめて返す',
            inputSchema: [
                'type' => 'object',
                'properties' => [
                    'grades' => [
                        'type' => 'array',
                        'items' => [
                            'type' => 'object',
                            'properties' => [
                                'index' => ['type' => 'integer', 'description' => '採点対象の問題番号(0始まり)'],
                                'score' => ['type' => 'integer', 'description' => '0〜100点の整数'],
                                'feedback' => ['type' => 'string', 'description' => '日本語での評価コメント'],
                            ],
                            'required' => ['index', 'score', 'feedback'],
                        ],
                    ],
                ],
                'required' => ['grades'],
            ],
        );
    }
}
