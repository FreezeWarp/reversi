<?php

namespace App;

/**
 * This models Reversi, implementing move checking extended from the base {@link Board} class.
 *
 * @author Joseph T. Parsons
 */
class Reversi extends Board
{

    /** @var boolean Whether the game has been forced to end by a player. If set, no moves are considered legal, and this class should be readonly. */
    private $hasGivenUp = false;

    /** @var array Offsets that form directional lines. All possible lines in a square grid are included. */
    const VALID_SHIFTS = [
        [-1, -1], // Up and left
        [0, -1], // Up
        [1, -1], // Up and right

        [-1, 0], // Left
        [1, 0], // Right

        [-1, 1], // Down and left
        [0, 1], // Down
        [1, 1], // Down and right
    ];


    /**
     * Create a Reversi board with a given width and height, and place the four starting pieces in the middle of the board.
     *
     * @param $width int The width (number of columns) of the board.
     * @param $height int The height (number of rows) of the board.
     */
    public function __construct(int $width = 8, int $height = 8) {
        parent::__construct($width, $height);

        /* Place the initial pieces on the board. */
        $this->setPiece(new Piece(Piece::WHITE), $width / 2 - 1, $height / 2 - 1);
        $this->setPiece(new Piece(Piece::BLACK), $width / 2,     $height / 2 - 1);
        $this->setPiece(new Piece(Piece::BLACK), $width / 2 - 1, $height / 2    );
        $this->setPiece(new Piece(Piece::WHITE), $width / 2,     $height / 2    );
    }



    /**
     * @param piece Piece The piece type to search for.
     *
     * @return int The number of matching pieces on the board, or the number of squares on the board if the opposite piece has none. (Naturally, garbage input results in garbage output: if there are somehow no pieces on the board, this will return the number of squares on the board regardless of the input.)
     */
    public function getPieceCount(Piece $piece) : int {
        return parent::getPieceCount($piece->opposite()) == 0
            ? $this->getHeight() * $this->getWidth()
            : parent::getPieceCount($piece);
    }


    /**
     * @return int The number of {@link Piece}.WHITE pieces on the board.
     */
    public function getWhitePieceCount() : int {
        return $this->getPieceCount(new Piece(Piece::WHITE));
    }

    /**
     * @return int The number of {@link Piece}.BLACK pieces on the board.
     */
    public function getBlackPieceCount() : int {
        return $this->getPieceCount(new Piece(Piece::BLACK));
    }


    /**
     * Determine whether, from the starting position, the line in the direction of [shiftX, shiftY] is formed of one colour of piece and then ended with the opposite color piece.
     *
     * @param $endingWith Piece The piece that should be at the end of the line.
     * @param $startX int The X coordinate to start forming a line at.
     * @param $startY int The Y coordinate to start forming a line at.
     * @param $shiftX int The X shift to use when searching for strings of pieces.
     * @param $shiftY int The Y shift to use when searching for strings of pieces.
     *
     * @return True iff a line of pieces matching the above criteria exists, false otherwise.
     */
    public function isLegalLine(Piece $endingWith, int $startX, int $startY, int $shiftX, int $shiftY) : bool {
        return !$this->hasGivenUp // The game mustn't have been ended manually.
            && $this->isInsideBoard($startX + $shiftX, $startY + $shiftY) // The target piece must be legal.
            && $this->getPiece($startX, $startY) == $endingWith->opposite() // Our starting piece must be opposite our ending piece.
            && ( // The next piece must either be...
                $this->getPiece($startX + $shiftX, $startY + $shiftY) == $endingWith // The ending piece.
                || $this->isLegalLine($endingWith, $startX + $shiftX, $startY + $shiftY, $shiftX, $shiftY) // Or a row of the opposite piece ending with the ending piece.
            );
    }


    /**
     * Get all legal lines, as defined by {@link Reversi#getLegalLines(edu.metrostate.ics425.jtp307.reversi.model.Piece, int, int)}, that exist for a given starting position and piece.
     * This will check the eight lines defined by {@link Reversi#VALID_SHIFTS}, and return some combination of them.
     *
     * @param $piece Piece The piece being played in the [X, Y] position.
     * @param $posX int The X position of the piece being played, 0-indexed.
     * @param $posY int The Y position of the piece being played, 0-indexed.
     *
     * @return array Some combination of {@link Reversi#VALID_SHIFTS}.
     */
    public function getLegalLines(Piece $piece, int $posX, int $posY) : array {
        $matchedLines = [];

        foreach (Reversi::VALID_SHIFTS AS $shiftPair) {
            if ($this->isLegalLine($piece, $posX + $shiftPair[0], $posY + $shiftPair[1], $shiftPair[0], $shiftPair[1])) {
                $matchedLines[] = $shiftPair;
            }
        }

        return $matchedLines;
    }


    /**
     * Determine whether a piece can be legally played in a given position.
     *
     * @param $piece Piece The piece being played in the [X, Y] position.
     * @param $posX int The X position of the piece being played, 0-indexed.
     * @param $posY int The Y position of the piece being played, 0-indexed.
     *
     * @return True if the given piece placement is legal, false otherwise.
     */
    public function isLegalMove(Piece $piece, int $posX, int $posY) : bool {
        return $this->getPiece($posX, $posY) == new Piece(Piece::BLANK)
            && count($this->getLegalLines($piece, $posX, $posY)) > 0;
    }


    /**
     * Set the piece whose turn it is on the board at the given location.
     * Will not do anything if the move is illegal.
     *
     * @param $posX int The x offset, 0-indexed.
     * @param $posY int The y offset, 0-indexed.
     *
     * @return True if the attempted move is legal, false otherwise.
     */
    public function setReversiPiece(int $posX, int $posY) : bool {
        $piece = $this->getCurrentPlayer();

        // Don't place on non-blank squares.
        if ($this->getPiece($posX, $posY) == new Piece(Piece::BLANK)) {
            $legalLines = $this->getLegalLines($piece, $posX, $posY);

            // Don't place if no legal lines are found.
            if (count($legalLines) > 0) {

                // Flip all pieces that are part of legal lines.
                foreach ($legalLines as $shiftPair) {
                    // Set the piece itself.
                    $this->setPiece($piece, $posX, $posY);

                    // Set all pieces in the line.
                    $posXLine = $posX;
                    $posYLine = $posY;

                    while ($this->getPiece($posXLine += $shiftPair[0], $posYLine += $shiftPair[1]) != $piece) {
                        $this->setPiece($piece, $posXLine, $posYLine);
                    }
                }

                // Skip the next player's turn if they have no available move.
                if (!$this->isMoveAvailable($this->getCurrentPlayer())) {
                    $this->skipTurn();
                }

                // Return true to indicate that a piece has been placed.
                return true;

            }
        }

        return false;
    }


    /**
     * @param piece The color of a piece being played.
     *
     * @return bool True if the given piece can be placed at some location on the board, false otherwise.
     */
    public function isMoveAvailable(Piece $piece) : bool {
        for ($row = 0; $row < $this->getHeight(); $row++) {
            for ($column = 0; $column < $this->getWidth(); $column++) {
                if ($this->isLegalMove($piece, $column, $row)) {
                    return true;
                }
            }
        }

        return false;
    }


    /**
     * @return bool True if the game is over (because no legal moves are available), false otherwewise.
     */
    public function isGameOver() : bool {
        return false;
        // When a piece is placed, the next player's turn is automatically skipped if no move is available. Thus, this will only be true when neither player has a legal move.
        return !$this->isMoveAvailable($this->getCurrentPlayer());
    }


    /**
     * Set the give up flag, ending the game prematurely.
     */
    public function giveUp() {
        $this->hasGivenUp = true;
    }
}