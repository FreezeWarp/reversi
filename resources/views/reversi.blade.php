<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=yes">
    <title>Reversi</title>

    <!-- Used for basic bootstrap styling. If not available, everything will
      -- still be functional, but ugly. -->
    <link
            rel="stylesheet"
            href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.2/css/bootstrap.min.css"
            integrity="sha384-PsH8R72JQ3SOdhVi3uxftmaW6Vc51MKb0q5P2rRUpPvrszuE4W1povHYgTpBfshb"
            crossorigin="anonymous">
    <script
            src="https://code.jquery.com/jquery-3.2.1.slim.min.js"
            integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN"
            crossorigin="anonymous"></script>

    <link rel="stylesheet" href="public/css/reversi.css" type="text/css"/>

</head>
<body>
<!-- Display Reversi Game -->
<div class="reversiBoardContainer" style="position: relative;">
    <div style="position: absolute; width: 100%;">

        <!-- If the game is over, display an overlay indicating as much, and prompt for a new game. -->
        @if ($reversiInstance->isGameOver())
            <div style="position: absolute; background-color: rgba(255, 255, 255, .75); min-height: 100%; width: 100%; text-align: center; vertical-align: middle;">
                <div class="jumbotron container" style="position: absolute; background: transparent; min-height: 100%; min-width: 100%; margin: 0; z-index: 2000;">
                    <h1 class="display-3" style="text-shadow: 2px 2px 10px #afafaf;">Game Over</h1>
                    <p style="text-shadow: 1px 1px 2px #9f9f9f;">
                        @if ($reversiInstance->getWhitePieceCount() > $reversiInstance->getBlackPieceCount())
                            White Wins, {{ $reversiInstance->getWhitePieceCount() }} - {{ $reversiInstance->getBlackPieceCount() }}
                        @elseif ($reversiInstance->getBlackPieceCount() > $reversiInstance->getWhitePieceCount())
                            Black Wins, {{ $reversiInstance->getBlackPieceCount() }} - {{ $reversiInstance->getWhitePieceCount() }}
                        @else
                            Tie Game
                        @endif
                    </p>

                    <hr class="my-4" />

                    <p class="lead">
                        <form method="get" action="newGame?width=8&height=8">
                            <button class="btn btn-primary btn-lg" style="opacity: .85;">New Game</button>
                        </form>
                    </p>
                </div>
            </div>
        @endif

        <!-- The Reversi board itself, including score information and new game, give up, and hint buttons. -->
        <table border="1" class="reversiBoard table table-striped table-bordered" style="width: auto; margin-left: auto; margin-right: auto;">


            <!-- Our score display. -->
            <tbody class="score">
            <tr class="table-sm">
                <th class="table-light" colspan="{{$reversiInstance->getWidth() * .5}}" style="text-align: center;">White Score</th>
                <th class="table-dark" colspan="{{$reversiInstance->getWidth() * .5}}" style="text-align: center;">Black Score</th>
            </tr>
            <tr class="table-sm">
                <td class="table-light" colspan="{{$reversiInstance->getWidth() * .5}}" style="text-align: center;">{{ $reversiInstance->getWhitePieceCount() }}</td>
                <td class="table-dark" colspan="{{$reversiInstance->getWidth() * .5}}" style="text-align: center;">{{ $reversiInstance->getBlackPieceCount() }}</td>
            </tr>
            </tbody>


            <!-- Our main board displaying, showing all pieces and empty squares representing the board. -->
            <tbody class="pieces">

                @foreach ($reversiInstance->getPieces() as $row)
                    <tr>

                        @foreach ($row as $column)
                            <td
                                align="center"
                                width="40"
                                @if ($column->name() == "BLANK")
                                    onmouseover="
                                    $(this).attr('data-oldClass', $('div', this).attr('class'));
                                    $('div', this).attr('class', '{{strtolower($reversiInstance->getCurrentPlayer()->name())}}Piece')
                                    "
                                    onmouseout="$('div', this).attr('class', $(this).attr('data-oldClass'));"
                                @endif
                            >

                            @if ($column->name() == "BLANK")
                                    <!-- While bad practice, I do think the overall user experience is more effective when using anchors instead of forms. Thus, we send our move coords with GET parameters instead of POST ones. -->
                                    <a href="move?yPos={{$loop->parent->index}}&xPos={{$loop->index}}" style="display: block; overflow: hidden;">
                                        <div class="
                                            blankPiece
                                            @if ($reversiInstance->isLegalMove($reversiInstance->getCurrentPlayer(), $loop->index, $loop->parent->index))
                                                {{strtolower($reversiInstance->getCurrentPlayer()->name())}}Piece hint
                                            @endif
                                        "></div>
                                    </a>
                            @else
                                <div class="{{strtolower($column->name())}}Piece"></div>
                            @endif
                        @endforeach

                    </tr>
                @endforeach

            </tbody>


            <!-- Display errors; consume any error messages placed in the user session. -->
            @if(Session::has('errMsgs'))
                <tbody class="errors">
                <tr class="table-danger">
                    <td colspan="{{$reversiInstance->getWidth()}}">
                        <h4>Errors Encountered!</h4>

                        <ul style="margin-bottom: 0px;">
                            @foreach (Session::get('errMsgs') AS $errMsg)
                                <li>{{$errMsg}}</li>
                            @endforeach
                        </ul>
                    </td>
                </tr>
                </tbody>

                <!-- Consume the error, preventing it from redisplaying. -->
                {{Session::put('errMsgs', null)}}
            @endif


            <!-- Display buttons for hints, new games, and quitting. -->
            <tbody class="utilities">
                <tr>
                    <td colspan="{{$reversiInstance->getWidth()}}">
                        <button
                            onclick="$('.hint').toggleClass('hintDisplay'); $(this).text($(this).text() === 'Show Hints' ? 'Hide Hints' : 'Show Hints');"
                            class="btn btn-info form-control"
                        >
                            Show Hints
                        </button>
                    </td>
                </tr>

                <form method="get" action="newGame">
                    <tr>
                        <td colspan="{{$reversiInstance->getWidth()}}">
                            <div class="row">
                                <div class="col-6">
                                    <label class="input-group">
                                        <span class="input-group-addon">Width</span>
                                        <input class="form-control" style="width: 3em;" name="width" value="8" />
                                    </label>
                                </div>
                                <div class="col-6">
                                    <label class="input-group">
                                        <span class="input-group-addon">Height</span>
                                        <input class="form-control" style="width: 3em;" name="height" value="8" />
                                    </label>
                                </div>
                            </div>

                            <input type="submit" value="Start a New Game!" class="form-control btn btn-success" />
                        </td>
                    </tr>
                </form>
            </tbody>


        </table>
    </div>
</div>
</body>
</html>