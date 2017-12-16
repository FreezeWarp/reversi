<?php
/**
 * A simple game board with a fixed width, fixed height, and containing pieces modelled by {@link Piece} -- that is, which may be one of two states, white or black.
 *
 * @author Joseph T. Parsons
 */

namespace App;

class Board
{
    
    /** @var int The total number of columns on the board. */
    private $width;

    /** @var int The total number of rows on the board. */
    private $height;

    /** @var Piece[][] The pieces on the board, grouped into rows and then seperated as columns. */
    private $pieces = [];

    /** @var Piece The colour of the last piece that was played. If a player's turn is skipped, this will be the skipped player.*/
    private $lastPiece = null;

    /** @var array The number pieces of a certain type on the board. */
    private $pieceCount = [
        Piece::BLANK => 0,
        Piece::WHITE => 0,
        Piece::BLACK => 0,
    ];
    
    
    /**
     * Create a board with a given width and height, and sets all pieces to {@link Piece}.BLANK.
     *
     * @param $width int The width (number of columns) of the board.
     * @param $height int The height (number of rows) of the board.
     */
    public function __construct(int $width = 8, int $height = 8) {
        $this->width = $width;
        $this->height = $height;
    
        // Set all pieces to Piece.BLANK.
        for($row = 0; $row < $this->height; $row++) {
            $this->pieces[$row] = [];

            for ($column = 0; $column < $width; $column++) {
                $this->setPiece(new Piece(Piece::BLANK), $column, $row);
            }
        }
    }
    
    
    
    /**
     * @return {@link Board#width}.
     */
    public function getWidth() : int {
        return $this->width;
    }
    
    
    /**
     * @return {@link Board#height}.
     */    
    public function getHeight() : int {
        return $this->height;
    }
    
    
    /**
     * @return Piece[][] {@link Board#pieces}.
     */
    public function getPieces() : array {
        return $this->pieces;
    }
    
    
    /**
     * @param $xPos int The x offset, 0-indexed.
     * @param $yPos int The y offset, 0-indexed.
     *
     * @return Piece The piece currently placed at the location.
     */
    public function getPiece(int $xPos, int $yPos) : ?Piece {
    if (!$this->isInsideBoard($xPos, $yPos)) {
        return null;
    }

    else {
        return $this->pieces[$yPos][$xPos] ?? null;
    }
}
    
    
    /**
     * @return {@link Board#lastPiece}
     */
    public function getLastPiece() : Piece {
        return $this->lastPiece;
    }

    
    /**
     * @param piece The piece type to search for.
     *
     * @return int The number of matching pieces on the board.
     */    
    public function getPieceCount(Piece $piece) : int {
        return $this->pieceCount[$piece->name()];
    }
    
    
    /**
     * @param $xPos int The x offset.
     * @param $yPos int The y offset.
     *
     * @return True if the given offsets are a valid location on the board, false otherwise.
     */
    public function isInsideBoard(int $xPos, int $yPos) : bool {
    return $xPos >= 0
        && $yPos >= 0
        && $xPos < $this->getWidth()
        && $yPos < $this->getHeight();
    }
    
    
    /**
     * Get the player who's turn it is to place pieces.
     *
     * @return Piece
     */
    public function getCurrentPlayer() : Piece {
        return $this->lastPiece->opposite();
    }
    
    
    /**
     * Place the given piece in the given position.
     *
     * @param $piece Piece The piece to set.
     * @param $xPos int The x offset, 0-indexed.
     * @param $yPos int The y offset, 0-indexed.
     */
    protected function setPiece(Piece $piece, int $xPos, int $yPos) {
        // Decrement the piece count from removing the old piece.
        $oldPiece = $this->getPiece($xPos, $yPos);

        if ($oldPiece) {
            $this->pieceCount[$oldPiece->name()] -= 1;
        }
        
        // Update the pieces array.
        $this->pieces[$yPos][$xPos] = $piece;
        
        // Update the last piece.
        $this->lastPiece = $piece;
        
        // Increment new piece count.
        $this->pieceCount[$piece->name()] += 1;
    }

    
    /**
     * Skip the current player's turn.
     */
    protected function skipTurn() {
        $this->lastPiece = $this->lastPiece->opposite();
    }
    
    
    /**
     * @return String containing very basic information about the game board.
     */
    public function __toString() : String {
        $string = "[Game Board; Width = " . $this->width . "; Height = " . $this->height . ";";
        
        foreach ($this->pieces AS $row) {
            $string .= "\n    |";

            foreach ($row AS $piece) {
                $string .= $piece->name() . "|";
            }
        }
        
        $string .= "\n]";
        
        return $string;
    }
}