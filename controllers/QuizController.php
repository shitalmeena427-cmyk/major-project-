<?php
require_once '../models/Quiz.php';
require_once '../models/Result.php';
require_once '../models/User.php';

class QuizController {
    private $quizModel;
    private $resultModel;
    private $userModel;

    public function __construct($pdo) {
        $this->quizModel = new Quiz($pdo);
        $this->resultModel = new Result($pdo);
        $this->userModel = new User($pdo);
    }

    public function getActiveQuizzes() {
        return $this->quizModel->getActive();
    }

    public function startQuiz($quiz_id, $user_performance) {
        // Adaptive: based on performance, choose difficulty
        $difficulty = $this->determineDifficulty($user_performance);
        return $this->quizModel->getQuestions($quiz_id, $difficulty);
    }

    private function determineDifficulty($performance) {
        if ($performance > 80) return 'hard';
        if ($performance > 50) return 'medium';
        return 'easy';
    }

    public function submitQuiz($user_id, $quiz_id, $answers, $time_taken) {
        $questions = $this->quizModel->getQuestions($quiz_id); // Get all for scoring
        $score = 0;
        $total_marks = 0;
        foreach ($questions as $q) {
            $total_marks += $q['marks'];
            if (isset($answers[$q['id']]) && $answers[$q['id']] == $q['correct_option']) {
                $score += $q['marks'];
            } elseif ($q['negative_marks'] > 0 && isset($answers[$q['id']])) {
                $score -= $q['negative_marks'];
            }
        }
        $percentage = ($score / $total_marks) * 100;
        $xp_earned = intval($percentage / 10); // 10 XP per 10%

        $this->resultModel->create([
            'user_id' => $user_id,
            'quiz_id' => $quiz_id,
            'score' => $score,
            'percentage' => $percentage,
            'time_taken' => $time_taken,
            'xp_earned' => $xp_earned
        ]);

        $this->userModel->updateXP($user_id, $xp_earned);

        return ['score' => $score, 'percentage' => $percentage, 'xp' => $xp_earned];
    }
}
?>