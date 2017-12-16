<?php
/**
 * Created by PhpStorm.
 * User: joseph
 * Date: 16/12/17
 * Time: 14:57
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Reversi;
use Illuminate\Support\Facades\Input;

class ReversiController
{

    /**
     * @var Reversi
     */
    private $reversiInstance;

    protected function setInstance(Request $request) {

        $reversiInstance = $request->session()->get('reversiInstance');

        if (!$reversiInstance) {
            $reversiInstance = new Reversi();
            $request->session()->put('reversiInstance', $reversiInstance);
        }

        $this->reversiInstance = $reversiInstance;

    }

    /**
     * Create a new game for the user.
     *
     * @param  int  $width
     * @param  int  $height
     *
     * @return Response
     */
    public function show(Request $request)
    {
        $this->setInstance($request);

        return view('reversi', ['reversiInstance' => $this->reversiInstance]);

    }


    public function move(Request $request) {

        $this->setInstance($request);


        /* Get Parameters */
        $xPos = (int) Input::get('xPos');
        $yPos = (int) Input::get('yPos');


        /* Process Parameters */
        if (!$this->reversiInstance->setReversiPiece($xPos, $yPos))
            $request->session()->put('errMsgs', ["That move is not legal."]);


        /* Send Redirect */
        return redirect('/');

    }


    public function newGame(Request $request) {

        /* Get Parameters */
        $width = (int) Input::get('width') ?: 8;
        $height = (int) Input::get('height') ?: 8;


        /* Validate Parameters */
        $errMsgs = [];

        if ($width < 4 || $width > 16 || $width % 2 != 0) {
            $errMsgs[] = "Width must be an even number between 4 and 16.";
        }

        if ($height < 4 || $height > 16 || $height % 2 != 0) {
            $errMsgs[] = "Height must be an even number between 4 and 16.";
        }


        /* Update Session */
        if (count($errMsgs) === 0)
            $request->session()->put('reversiInstance', new Reversi($width, $height));
        else
            $request->session()->put('errMsgs', $errMsgs);


        /* Send Redirect */
        return redirect('/');

    }

}