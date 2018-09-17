<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MinimaxController extends Controller
{

    static $minPlayer = 'X';
    static $maxPlayer = 'O';

    public function move(Request $request)
    {
        $board = $request->input('board');
        // поиск лучшего хода нужен только для компьютера
        $result  = $this->minimax($board, self::$maxPlayer, true);
        logger($result);
        return $result['move'];
    }

    protected function minimax($board, $player, $isRoot=false)
    {

        if ($score = $this->isTerminal($board)) {
            return $score;
        }

        $availableCells = $this->emptyCells($board);
        $flipBoard      = array_flip($board); // нужно для получения индекса ячейки по значению
        $moves          = []; // Содержит все ходы с просчитанным рейтингом для каждого
        $inversedPlayer = $player == self::$maxPlayer ? self::$minPlayer : self::$maxPlayer;

        foreach ($availableCells as $index => $cell) { // поиск лучшего хода для каждой ячейки
            // сделать ход
            $cellIndex         = $flipBoard[$cell]; // Индекс ячейки, в которую делается ход
            $board[$cellIndex] = $player;

            $score = $this->minimax($board, $inversedPlayer); // просчитать рейтинг
            $board[$cellIndex] = $cell;
            $moves[$cellIndex] = $score; // добавить просчитанный ход в список
        }

        // Выбор хода с лучшим рейтингом
        if ($player === self::$maxPlayer) {
            $bestScore = -10000;
            foreach ($moves as $moveIndex => $score) {
                if ($score > $bestScore) {
                    $bestScore = $score;
                    $bestMove = $moveIndex;
                }
            }
        } else {
            $bestScore = 10000;
            foreach ($moves as $moveIndex => $move) {
                if ($score < $bestScore) {
                    $bestScore = $score;
                    $bestMove = $moveIndex;
                }
            }
        }

        return $isRoot ? $bestMove : $bestScore;
    }

    protected function isTerminal($board)
    {
        if ($this->winning($board, self::$minPlayer)) {
          return -1;
        } else if ($this->winning($board, self::$maxPlayer)) {
          return 1;
        }

        return false;
    }

    protected function winning($board, $player)
    {
        if (
            ($board[0] == $player && $board[1] == $player && $board[2] == $player) ||
            ($board[3] == $player && $board[4] == $player && $board[5] == $player) ||
            ($board[6] == $player && $board[7] == $player && $board[8] == $player) ||
            ($board[0] == $player && $board[3] == $player && $board[6] == $player) ||
            ($board[1] == $player && $board[4] == $player && $board[7] == $player) ||
            ($board[2] == $player && $board[5] == $player && $board[8] == $player) ||
            ($board[0] == $player && $board[4] == $player && $board[8] == $player) ||
            ($board[2] == $player && $board[4] == $player && $board[6] == $player)
        ) {
            return true;
        } else {
            return false;
        }
    }

    protected function emptyCells($board)
    {
        return array_filter($board, function ($s) {
            return $s != self::$minPlayer && $s != self::$maxPlayer;
        });
    }
}