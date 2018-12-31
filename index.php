<?php

ini_set('max_execution_time', (60*60*24)); //300 seconds = 5 minutes

class Lotto
{
    public $winning_lotto_numbers;
    public $winning_lotto_numbers_inc_bonus_ball;
    public $bonus_ball_value;
    public $player_lotto_numbers;
    public $days_played = 0;
    public $lowest_winning_number = 1;
    public $highest_winning_number = 59;

    public $lotto_sheet_no = 0;

    public $price_per_play = 2;
    public $amount_spent_on_lotto = 0;
    public $amount_won = 0;

    public $amount_lucky_dips = 0;


    public $prizes = [
        '2_MAIN_NUMBERS' => 'FREE LUCKY DIP',
        '3_MAIN_NUMBERS' => 30,
        '4_MAIN_NUMBERS' => 140,
        '5_MAIN_NUMBERS' => 1750,
        '5_MAIN_NUMBERS_INC_BONUS_BALL' => 1000000,
        'JACKPOT' => 2000000,
    ];

    public function set_winning_lotto_numbers()
    {
        unset($this->winning_lotto_numbers);
        unset($this->bonus_ball_value);
        unset($this->winning_lotto_numbers_inc_bonus_ball);

        $this->winning_lotto_numbers = $this->generate_lotto_numbers();
        $this->bonus_ball_value = $this->generate_bonus_ball();
        $this->winning_lotto_numbers_inc_bonus_ball = $this->generate_lotto_numbers_inc_bonus_ball();
    }

    public function player_lotto_numbers()
    {
        unset($this->player_lotto_number);
        $this->generate_lotto_numbers();
        $this->winning_lotto_numbers = $this->generate_lotto_numbers();
    }

    public function generate_lotto_numbers()
    {
        $lotto_numbers = array();

        for ($i = 0; $i < 6; $i++)
        {
            do
            {
                $random_number = rand(1,59);
            } while (in_array($random_number, $lotto_numbers));

            $lotto_numbers[] = $random_number;
        }

        sort($lotto_numbers);

        return $lotto_numbers;
    }

    private function generate_bonus_ball()
    {
        do
        {
            $bonus_ball = rand($this->lowest_winning_number, $this->highest_winning_number);
        } while (in_array($bonus_ball, $this->winning_lotto_numbers));

        return $bonus_ball;
    }

    private function generate_lotto_numbers_inc_bonus_ball()
    {
         $array = $this->winning_lotto_numbers;
         $array[] = $this->bonus_ball_value;

         return $array;
    }

    public function generate_player_lotto_sheet($amount = 10)
    {
        $sheet = array();

        for ($i = 0; $i < $amount; $i++)
        {
            $sheet[] = $this->generate_lotto_numbers();
        }

        return $sheet;
    }

    public function print_pre($value){
        echo '<pre>';
        print_r($value);
        echo '</pre>';
    }

    public function calculate_ymd($number_of_days)
    {
        $year = (int)($number_of_days / 365);
        $week = (int)(($number_of_days % 365) / 7);
        $days = ($number_of_days % 365) % 7;
        $period = ("Years = " . $year . ", Weeks = " . $week . ", Days = " . $days);
        return $period;
    }

    public function display_winning_numbers()
    {
        $html = '<header class="sticky">';
        $html .= '<ul class="winning_top_numbers">';
        foreach ($this->winning_lotto_numbers as $winning_lotto_number)
        {
            $html .= '<li><span>' . $winning_lotto_number .'</span></li>';
        }
        $html .= '<li><span class="bonus-ball">' . $this->bonus_ball_value .'</span></li>';
        $html .= '</ul>';
        $html .= '</header>';

        echo $html;
    }

    public function display_lotto_sheet($player_lotto_sheet, $lucky_dip = TRUE)
    {
        $html = "<table class='table'>";
        $html .= "<tr><th colspan='6'>Ticket no:" . $this->lotto_sheet_no . "</th></tr>";

        $html .= $this->generate_lotto_rows($player_lotto_sheet);

        if($this->amount_lucky_dips > 0)
        {
            $html .= $this->play_free_lucky_dips($this->amount_lucky_dips);
            $this->amount_lucky_dips = 0;
        }

        $html .= $this->display_stats();

        $html .= "</table>";

        echo $html;
    }

    public function generate_lotto_rows($player_lotto_sheet, $lucky_dip_active = TRUE)
    {
        $html = '';

        foreach ($player_lotto_sheet as $lotto_sheet_row):
            $increment = 0;
            $amount_numbers_correct = 0;

            if($lucky_dip_active):
                $this->amount_spent_on_lotto += $this->price_per_play;
            endif;

            foreach ($lotto_sheet_row as $single_lotto_number):

                $increment++;

                if($increment == 1):
                    $html .= '<tr>';
                endif;

                $highlight_if_correct = '';
                if(in_array($single_lotto_number, $this->winning_lotto_numbers_inc_bonus_ball))
                {
                    $highlight_if_correct = 'highlight';
                    $amount_numbers_correct++;

                    if($single_lotto_number == $this->bonus_ball_value):
                        $highlight_if_correct = 'highlight_bonus_ball';
                    endif;
                }

                $html .= "<td><div class='" . $highlight_if_correct . "'>" . $single_lotto_number . "</div></td>";

                if($increment == 6):
                    $html .= $this->show_message_if_won($amount_numbers_correct, $lucky_dip_active);
                    $html .= '</tr>';
                endif;

            endforeach;
        endforeach;

        return $html;
    }

    public function show_message_if_won($amount_numbers_correct,  $lucky_dip_active = TRUE)
    {
        $html = '';

        if($amount_numbers_correct >= 2):

            $message = '';
            switch ($amount_numbers_correct) :
                case 3:
                    $message = 'You won £' . $this->prizes['3_MAIN_NUMBERS'];
                    $this->amount_won += $this->prizes['3_MAIN_NUMBERS'];
                    break;
                case 4:
                    $message = 'You won £' . $this->prizes['4_MAIN_NUMBERS'];
                    $this->amount_won += $this->prizes['4_MAIN_NUMBERS'];
                    break;
                case 5:
                    $message = 'You won £' . $this->prizes['5_MAIN_NUMBERS'];
                    $this->amount_won += $this->prizes['5_MAIN_NUMBERS'];;
                    break;
                case 6:
                    $message = 'You won £' . $this->prizes['5_MAIN_NUMBERS_INC_BONUS_BALL'];
                    $this->amount_won += $this->prizes['5_MAIN_NUMBERS_INC_BONUS_BALL'];
                    break;
                default:
                    if($lucky_dip_active)
                    {
                        $message = $this->prizes['2_MAIN_NUMBERS'];
                        $this->amount_lucky_dips++;
                    }
                    break;
            endswitch;

            if($lucky_dip_active):
                $html .= '<td class="row_won">' . $message . '</td>';
            elseif ($amount_numbers_correct >=  3):
                $html .= '<td class="row_won">' . $message . '</td>';
            endif;

        endif;

        return $html;
    }

    public function display_stats()
    {
        $days_played = $this->days_played++;
        $profit_loss = $this->amount_won - $this->amount_spent_on_lotto;
        $profit_loss_text = ($profit_loss > 0) ? '<span class="profit">(Profit)</span>' : '<span class="loss">(Loss)</span>';

        $stats = '<small>';
        $stats .= 'Duration: ' . $this->calculate_ymd($days_played) . '<br>';
        $stats .= 'Total winnings = £' . number_format($this->amount_won) . '<br>';
        $stats .= 'Total amount spent = £' . number_format($this->amount_spent_on_lotto) . '<br>';
        $stats .= 'Profit/Loss = £' . number_format($profit_loss) . ' ' . $profit_loss_text . '<br>';
        $stats .= '</small>';

        $html = '<tr><td class="stats" colspan="6">';
        $html .= $stats;
        $html .= '</td></tr>';

        return $html;
    }

    public function play_free_lucky_dips($amount)
    {
        $player_won = false;
        $lucky_dip_lotto_sheet = $this->generate_player_lotto_sheet($amount);

        $html = '<tr><td colspan="6" class="free_lucky_dip"><h4>Extra Lucky Dip x' . $amount . '</h4></td></tr>';
        $html .= $this->generate_lotto_rows($lucky_dip_lotto_sheet, FALSE);

        foreach ($lucky_dip_lotto_sheet as $lotto_sheet_row):
            if($this->winning_lotto_numbers == $lotto_sheet_row):
                $player_won = true;
            endif;
        endforeach;

        if($player_won){
            echo "<script>$('#you_won_modal').modal('show');</script>";
            exit();
        }

        return $html;
    }

    public function place_row_seperator()
    {
        if($this->days_played % 3 == 0):
            echo '<div class="col-lg-12 visible-lg hidden-md hidden-sm hidden-xs"></div>';
        endif;

        if($this->days_played % 2 ==0):
            echo '<div class="col-md-12 visible-md hidden-lg hidden-sm hidden-xs"></div>';
        endif;

        echo '<div class="col-xs-12 visible-xs  hidden-lg  hidden-md hidden-sm"></div>';
    }

    public function compare_sheet_against_winning_numbers()
    {
        $this->set_winning_lotto_numbers();
        $player_won = false;

        $this->display_winning_numbers();

        do
        {
            $player_lotto_sheet = $this->generate_player_lotto_sheet();

            /* UNCOMMENT BELOW TO FAKE A LOTTO WIN */
            /*
            if($this->days_played == 10):
                $player_lotto_sheet[9] = $this->winning_lotto_numbers;
            endif;
            */

            foreach ($player_lotto_sheet as $lotto_sheet_row):



                if($this->winning_lotto_numbers == $lotto_sheet_row):
                    $player_won = true;
                    echo "<script>$('#you_won_modal').modal('show');</script>";
                endif;
            endforeach;

            echo '<div class="col-lg-4 col-md-6 col-xs-12" style="margin-top: 30px;">';

            $this->lotto_sheet_no++;
            $this->display_lotto_sheet($player_lotto_sheet);

            echo '</div>';

            $this->place_row_seperator();

        } while (!$player_won);

        exit();
    }
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Lucky Lotto</title>
    <link href="https://fonts.googleapis.com/css?family=VT323" rel="stylesheet">

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/lotto_styles.css">

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>

    <style>
        header.sticky {
            position: -webkit-sticky; /* Safari */
            position: sticky;
            top: 50px;
            z-index: 999990;
        }
    </style>
</head>
<body>
    <?php $lotto = new Lotto(); ?>
    <div class="modal fade" id="you_won_modal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">Congratulations!</h4>
                </div>
                <div class="modal-body">
                    <p>You actually won the lottery!</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <nav class="navbar navbar-inverse navbar-fixed-top">
        <div class="container">
            <!-- Brand and toggle get grouped for better mobile display -->
            <div class="navbar-header">
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="#">Lucky Lotto</a>
            </div>

            <!-- Collect the nav links, forms, and other content for toggling -->
            <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                <ul class="nav navbar-nav">
                    <li class="active"><a href="#">Play</a></li>
                    <li><a href="#">About</a></li>
                    <li><a href="#">Latest Winners</a></li>
                    <li><a href="#">Our Charities</a></li>
                </ul>
                <ul class="nav navbar-nav navbar-right">
                    <li><a href="#">Login / Register</a></li>
                    <!--<li class="dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Dropdown <span class="caret"></span></a>
                        <ul class="dropdown-menu">
                            <li><a href="#">Action</a></li>
                            <li><a href="#">Another action</a></li>
                            <li><a href="#">Something else here</a></li>
                            <li role="separator" class="divider"></li>
                            <li><a href="#">Separated link</a></li>
                        </ul>
                    </li>-->
                </ul>
            </div><!-- /.navbar-collapse -->
        </div><!-- /.container-fluid -->
    </nav>
    <div class="container body round-top-corners">
        <div class="row">
            <div style="margin-top: -1px;">
                <img src="img/lotteryheader.jpg" class="img-responsive round-top-corners" />
            </div>
            <?php
                $lotto->compare_sheet_against_winning_numbers();
            ?>
        </div>
    </div>
</body>
</html>