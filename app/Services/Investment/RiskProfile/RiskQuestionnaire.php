<?php

declare(strict_types=1);

namespace App\Services\Investment\RiskProfile;

/**
 * Risk Questionnaire
 * Provides comprehensive risk tolerance assessment questions
 *
 * Question Categories:
 * - Risk Tolerance (emotional response to volatility)
 * - Investment Experience
 * - Time Horizon
 * - Financial Goals
 * - Reaction to Loss
 * - Market Knowledge
 *
 * Scoring: Each answer weighted 1-5 (Conservative to Aggressive)
 * Final Score: 1-10 scale derived from weighted answers
 */
class RiskQuestionnaire
{
    /**
     * Get complete risk questionnaire
     *
     * @return array Questionnaire with all questions
     */
    public function getQuestionnaire(): array
    {
        return [
            'title' => 'Investment Risk Profile Questionnaire',
            'description' => 'This questionnaire helps determine your investment risk profile. Answer honestly based on your genuine feelings and circumstances.',
            'sections' => [
                $this->getRiskToleranceQuestions(),
                $this->getInvestmentExperienceQuestions(),
                $this->getTimeHorizonQuestions(),
                $this->getFinancialGoalsQuestions(),
                $this->getReactionToLossQuestions(),
                $this->getMarketKnowledgeQuestions(),
            ],
            'total_questions' => $this->getTotalQuestionCount(),
            'scoring_info' => $this->getScoringInfo(),
        ];
    }

    /**
     * Calculate risk score from answers
     *
     * @param  array  $answers  User answers (question_id => answer_id)
     * @return array Risk score and breakdown
     */
    public function calculateRiskScore(array $answers): array
    {
        $questionnaire = $this->getQuestionnaire();
        $totalScore = 0;
        $maxScore = 0;
        $categoryScores = [];

        foreach ($questionnaire['sections'] as $section) {
            $categoryScore = 0;
            $categoryMax = 0;

            foreach ($section['questions'] as $question) {
                $questionId = $question['id'];
                $answerId = $answers[$questionId] ?? null;

                if ($answerId === null) {
                    continue;
                }

                // Find selected answer's score
                $selectedAnswer = collect($question['answers'])->firstWhere('id', $answerId);

                if ($selectedAnswer) {
                    $score = $selectedAnswer['score'];
                    $totalScore += $score;
                    $categoryScore += $score;
                }

                $categoryMax += 5; // Max score per question
            }

            $maxScore += $categoryMax;

            if ($categoryMax > 0) {
                $categoryScores[$section['category']] = [
                    'score' => $categoryScore,
                    'max' => $categoryMax,
                    'percentage' => round(($categoryScore / $categoryMax) * 100, 1),
                ];
            }
        }

        // Convert to 1-10 scale
        $normalizedScore = $maxScore > 0 ? (($totalScore / $maxScore) * 9) + 1 : 5;

        return [
            'raw_score' => $totalScore,
            'max_score' => $maxScore,
            'normalized_score' => round($normalizedScore, 1),
            'percentage' => $maxScore > 0 ? round(($totalScore / $maxScore) * 100, 1) : 0,
            'category_scores' => $categoryScores,
            'questions_answered' => count($answers),
            'total_questions' => $this->getTotalQuestionCount(),
            'completion_percent' => round((count($answers) / $this->getTotalQuestionCount()) * 100, 1),
        ];
    }

    /**
     * Get risk tolerance questions
     *
     * @return array Risk tolerance section
     */
    private function getRiskToleranceQuestions(): array
    {
        return [
            'category' => 'risk_tolerance',
            'title' => 'Risk Tolerance',
            'description' => 'How comfortable are you with investment volatility?',
            'questions' => [
                [
                    'id' => 'rt_1',
                    'question' => 'How would you describe your investment risk tolerance?',
                    'type' => 'single_choice',
                    'answers' => [
                        ['id' => 'rt_1_a', 'text' => 'Very conservative - I want to avoid losses at all costs', 'score' => 1],
                        ['id' => 'rt_1_b', 'text' => 'Conservative - I prefer stable returns with minimal volatility', 'score' => 2],
                        ['id' => 'rt_1_c', 'text' => 'Moderate - I can accept some volatility for better returns', 'score' => 3],
                        ['id' => 'rt_1_d', 'text' => 'Aggressive - I am comfortable with significant volatility', 'score' => 4],
                        ['id' => 'rt_1_e', 'text' => 'Very aggressive - I actively seek high-risk investments', 'score' => 5],
                    ],
                ],
                [
                    'id' => 'rt_2',
                    'question' => 'If your portfolio dropped 20% in value over 3 months, what would you do?',
                    'type' => 'single_choice',
                    'answers' => [
                        ['id' => 'rt_2_a', 'text' => 'Sell everything immediately to prevent further losses', 'score' => 1],
                        ['id' => 'rt_2_b', 'text' => 'Move to lower-risk investments', 'score' => 2],
                        ['id' => 'rt_2_c', 'text' => 'Hold and wait for recovery', 'score' => 3],
                        ['id' => 'rt_2_d', 'text' => 'Buy more at lower prices', 'score' => 4],
                        ['id' => 'rt_2_e', 'text' => 'Invest significantly more to maximize gains on recovery', 'score' => 5],
                    ],
                ],
                [
                    'id' => 'rt_3',
                    'question' => 'Which statement best describes your investment philosophy?',
                    'type' => 'single_choice',
                    'answers' => [
                        ['id' => 'rt_3_a', 'text' => 'Preservation of capital is most important', 'score' => 1],
                        ['id' => 'rt_3_b', 'text' => 'Steady income is my priority', 'score' => 2],
                        ['id' => 'rt_3_c', 'text' => 'Balanced growth with moderate income', 'score' => 3],
                        ['id' => 'rt_3_d', 'text' => 'Long-term capital growth is my goal', 'score' => 4],
                        ['id' => 'rt_3_e', 'text' => 'Maximum growth potential, even with high risk', 'score' => 5],
                    ],
                ],
            ],
        ];
    }

    /**
     * Get investment experience questions
     *
     * @return array Investment experience section
     */
    private function getInvestmentExperienceQuestions(): array
    {
        return [
            'category' => 'experience',
            'title' => 'Investment Experience',
            'description' => 'Your knowledge and experience with investments',
            'questions' => [
                [
                    'id' => 'exp_1',
                    'question' => 'How long have you been investing?',
                    'type' => 'single_choice',
                    'answers' => [
                        ['id' => 'exp_1_a', 'text' => 'Never invested before', 'score' => 1],
                        ['id' => 'exp_1_b', 'text' => 'Less than 2 years', 'score' => 2],
                        ['id' => 'exp_1_c', 'text' => '2-5 years', 'score' => 3],
                        ['id' => 'exp_1_d', 'text' => '5-10 years', 'score' => 4],
                        ['id' => 'exp_1_e', 'text' => 'More than 10 years', 'score' => 5],
                    ],
                ],
                [
                    'id' => 'exp_2',
                    'question' => 'How would you rate your investment knowledge?',
                    'type' => 'single_choice',
                    'answers' => [
                        ['id' => 'exp_2_a', 'text' => 'Beginner - Limited understanding', 'score' => 1],
                        ['id' => 'exp_2_b', 'text' => 'Basic - Understand simple concepts', 'score' => 2],
                        ['id' => 'exp_2_c', 'text' => 'Intermediate - Good understanding', 'score' => 3],
                        ['id' => 'exp_2_d', 'text' => 'Advanced - Strong knowledge', 'score' => 4],
                        ['id' => 'exp_2_e', 'text' => 'Expert - Professional level', 'score' => 5],
                    ],
                ],
                [
                    'id' => 'exp_3',
                    'question' => 'Which investments have you held in the past 5 years?',
                    'type' => 'single_choice',
                    'answers' => [
                        ['id' => 'exp_3_a', 'text' => 'Only savings accounts', 'score' => 1],
                        ['id' => 'exp_3_b', 'text' => 'Savings and bonds', 'score' => 2],
                        ['id' => 'exp_3_c', 'text' => 'Funds and bonds', 'score' => 3],
                        ['id' => 'exp_3_d', 'text' => 'Funds, bonds, and individual stocks', 'score' => 4],
                        ['id' => 'exp_3_e', 'text' => 'Wide range including alternatives and derivatives', 'score' => 5],
                    ],
                ],
            ],
        ];
    }

    /**
     * Get time horizon questions
     *
     * @return array Time horizon section
     */
    private function getTimeHorizonQuestions(): array
    {
        return [
            'category' => 'time_horizon',
            'title' => 'Time Horizon',
            'description' => 'When will you need access to your investments?',
            'questions' => [
                [
                    'id' => 'th_1',
                    'question' => 'When do you expect to start withdrawing from this portfolio?',
                    'type' => 'single_choice',
                    'answers' => [
                        ['id' => 'th_1_a', 'text' => 'Within 1 year', 'score' => 1],
                        ['id' => 'th_1_b', 'text' => '1-3 years', 'score' => 2],
                        ['id' => 'th_1_c', 'text' => '3-5 years', 'score' => 3],
                        ['id' => 'th_1_d', 'text' => '5-10 years', 'score' => 4],
                        ['id' => 'th_1_e', 'text' => 'More than 10 years', 'score' => 5],
                    ],
                ],
                [
                    'id' => 'th_2',
                    'question' => 'Over what period will you withdraw from this portfolio?',
                    'type' => 'single_choice',
                    'answers' => [
                        ['id' => 'th_2_a', 'text' => 'All at once', 'score' => 1],
                        ['id' => 'th_2_b', 'text' => 'Over 1-2 years', 'score' => 2],
                        ['id' => 'th_2_c', 'text' => 'Over 3-5 years', 'score' => 3],
                        ['id' => 'th_2_d', 'text' => 'Over 6-10 years', 'score' => 4],
                        ['id' => 'th_2_e', 'text' => 'Over more than 10 years', 'score' => 5],
                    ],
                ],
            ],
        ];
    }

    /**
     * Get financial goals questions
     *
     * @return array Financial goals section
     */
    private function getFinancialGoalsQuestions(): array
    {
        return [
            'category' => 'goals',
            'title' => 'Financial Goals',
            'description' => 'What are your investment objectives?',
            'questions' => [
                [
                    'id' => 'goal_1',
                    'question' => 'What is your primary investment goal?',
                    'type' => 'single_choice',
                    'answers' => [
                        ['id' => 'goal_1_a', 'text' => 'Capital preservation - Maintain current wealth', 'score' => 1],
                        ['id' => 'goal_1_b', 'text' => 'Income generation - Regular withdrawals', 'score' => 2],
                        ['id' => 'goal_1_c', 'text' => 'Balanced growth and income', 'score' => 3],
                        ['id' => 'goal_1_d', 'text' => 'Capital growth - Build wealth over time', 'score' => 4],
                        ['id' => 'goal_1_e', 'text' => 'Aggressive growth - Maximum returns', 'score' => 5],
                    ],
                ],
                [
                    'id' => 'goal_2',
                    'question' => 'What return do you expect from your investments annually?',
                    'type' => 'single_choice',
                    'answers' => [
                        ['id' => 'goal_2_a', 'text' => '0-2% (preservation)', 'score' => 1],
                        ['id' => 'goal_2_b', 'text' => '2-4% (conservative)', 'score' => 2],
                        ['id' => 'goal_2_c', 'text' => '4-6% (moderate)', 'score' => 3],
                        ['id' => 'goal_2_d', 'text' => '6-10% (growth)', 'score' => 4],
                        ['id' => 'goal_2_e', 'text' => 'Over 10% (aggressive)', 'score' => 5],
                    ],
                ],
            ],
        ];
    }

    /**
     * Get reaction to loss questions
     *
     * @return array Reaction to loss section
     */
    private function getReactionToLossQuestions(): array
    {
        return [
            'category' => 'loss_reaction',
            'title' => 'Reaction to Loss',
            'description' => 'How do you handle investment losses?',
            'questions' => [
                [
                    'id' => 'loss_1',
                    'question' => 'Your portfolio has lost 10% in one month. How do you feel?',
                    'type' => 'single_choice',
                    'answers' => [
                        ['id' => 'loss_1_a', 'text' => 'Extremely anxious - I cannot sleep', 'score' => 1],
                        ['id' => 'loss_1_b', 'text' => 'Very concerned - This worries me significantly', 'score' => 2],
                        ['id' => 'loss_1_c', 'text' => 'Somewhat concerned but not panicking', 'score' => 3],
                        ['id' => 'loss_1_d', 'text' => 'Not worried - This is normal volatility', 'score' => 4],
                        ['id' => 'loss_1_e', 'text' => 'Excited - This is a buying opportunity', 'score' => 5],
                    ],
                ],
                [
                    'id' => 'loss_2',
                    'question' => 'What is the maximum loss you could tolerate over a year?',
                    'type' => 'single_choice',
                    'answers' => [
                        ['id' => 'loss_2_a', 'text' => 'None - I cannot accept any losses', 'score' => 1],
                        ['id' => 'loss_2_b', 'text' => 'Up to 5% loss', 'score' => 2],
                        ['id' => 'loss_2_c', 'text' => 'Up to 15% loss', 'score' => 3],
                        ['id' => 'loss_2_d', 'text' => 'Up to 30% loss', 'score' => 4],
                        ['id' => 'loss_2_e', 'text' => 'Over 30% - I focus on long-term gains', 'score' => 5],
                    ],
                ],
            ],
        ];
    }

    /**
     * Get market knowledge questions
     *
     * @return array Market knowledge section
     */
    private function getMarketKnowledgeQuestions(): array
    {
        return [
            'category' => 'knowledge',
            'title' => 'Market Knowledge',
            'description' => 'Understanding of financial markets',
            'questions' => [
                [
                    'id' => 'know_1',
                    'question' => 'How often do you monitor your investments?',
                    'type' => 'single_choice',
                    'answers' => [
                        ['id' => 'know_1_a', 'text' => 'Never or rarely', 'score' => 1],
                        ['id' => 'know_1_b', 'text' => 'Annually', 'score' => 2],
                        ['id' => 'know_1_c', 'text' => 'Quarterly', 'score' => 3],
                        ['id' => 'know_1_d', 'text' => 'Monthly', 'score' => 4],
                        ['id' => 'know_1_e', 'text' => 'Daily or weekly', 'score' => 5],
                    ],
                ],
                [
                    'id' => 'know_2',
                    'question' => 'How do you make investment decisions?',
                    'type' => 'single_choice',
                    'answers' => [
                        ['id' => 'know_2_a', 'text' => 'I rely entirely on others for advice', 'score' => 1],
                        ['id' => 'know_2_b', 'text' => 'I follow advisor recommendations', 'score' => 2],
                        ['id' => 'know_2_c', 'text' => 'I review options and decide with guidance', 'score' => 3],
                        ['id' => 'know_2_d', 'text' => 'I research and make independent decisions', 'score' => 4],
                        ['id' => 'know_2_e', 'text' => 'I actively manage and trade my portfolio', 'score' => 5],
                    ],
                ],
            ],
        ];
    }

    /**
     * Get total question count
     *
     * @return int Total questions
     */
    private function getTotalQuestionCount(): int
    {
        return 15; // 3 + 3 + 2 + 2 + 2 + 2 + 1 (optional)
    }

    /**
     * Get scoring information
     *
     * @return array Scoring info
     */
    private function getScoringInfo(): array
    {
        return [
            'scale' => '1-10',
            'scale_description' => [
                '1-2' => 'Very Conservative',
                '3-4' => 'Conservative',
                '5-6' => 'Moderate',
                '7-8' => 'Growth',
                '9-10' => 'Aggressive',
            ],
            'weighting' => 'All questions equally weighted (1-5 points each)',
        ];
    }
}
