<?php

namespace App\Enums;

/**
 * 採点の厳しさ3段階(要件定義3.3)。回答セッション開始時に毎回選択し、
 * Attempt単位で1つ記録する(同じ挑戦内の全問に共通のレベルが適用されるため)。
 */
enum GradingLevel: string
{
    case Easy = 'easy';
    case Normal = 'normal';
    case Hard = 'hard';

    /**
     * 画面表示用の日本語ラベル。
     */
    public function label(): string
    {
        return match ($this) {
            self::Easy => '優しい',
            self::Normal => '普通',
            self::Hard => '厳しい',
        };
    }

    /**
     * Claude APIへのsystem prompt内で使う採点方針の文言。
     * レベルごとに固定文言にすることで、AIへの指示のブレを抑える(要件定義3.3)。
     */
    public function policyText(): string
    {
        return match ($this) {
            self::Easy => '優しめに採点してください。多少の言葉足らずや表現の粗さは減点せず、趣旨が伝わっていれば高めの点数をつけてください。',
            self::Normal => '標準的な基準で採点してください。過度に甘くも厳しくもしないでください。',
            self::Hard => '厳しめに採点してください。曖昧な表現・論理の飛躍・説明不足を見逃さず、完成度が高い解答でなければ高得点をつけないでください。',
        };
    }
}
