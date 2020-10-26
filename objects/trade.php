<?php
include_once '../config/core.php';
class Trade{
  
    // database connection and table name
    private $conn;
    private $table_name = "transactions_trades";
  
    // object properties
    public $id;
    public $amount;
    public $profit_loss;
    public $content;
  
    // constructor with $db as database connection
    public function __construct($db){
      	date_default_timezone_set('UTC');
        $this->conn = $db;
        $database = new ApiConfig();
        $this->table_trades = $database->dbprefix.$this->table_name;
        $this->dbprefix = $database->dbprefix;
        $this->timezone = $database->offset;
    }

    // Function get list trade today
    function getAmountToday($user_id, $time_start_day, $time_end_day, $is_demo = false)
    {
        if($is_demo == 'true') {
            $table_demo_trade = $this->dbprefix.'user_demo_trades';
        } else {
            $table_demo_trade = $this->dbprefix.'user_trades';
        }
        // select all query
        $query = "SELECT t.id, t.amount AS trade_amount, t.profit_loss, t.trade_status, t.time_finalised, t.fee_percent, t.change_percen, t.reset_percen, ut.amount_trade,
                    t.balance_before_trade AS trade_balance_before_trade,
                    ut.balance_after_trade_update, ut.percen AS user_percen, 
                    ut.balance_before_trade AS user_balance_before_trade 
                    FROM " . $this->table_trades . " t
                    LEFT JOIN ".$table_demo_trade." ut ON ut.trade_id = t.id
                    WHERE ut.user_id = ? AND t.trade_status != 0 AND t.type = 'trade' AND t.time_finalised <= '". date('Y-m-d H:i:s', $time_end_day)."'
                    AND t.time_finalised >= '". date('Y-m-d H:i:s', $time_start_day). "' ORDER BY t.time_finalised ASC";
        // prepare query statement
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $user_id);
        // execute query
        $stmt->execute();
        return $stmt;
    }

    // function get last trade yesterday
    function getLastTradeYesterday($account_id, $last_date, $is_demo = false)
    {
        if($is_demo == 'true') {
            $table_demo_trade = $this->dbprefix.'user_demo_trades';
        } else {
            $table_demo_trade = $this->dbprefix.'user_trades';
        }
        $last_hy = date('Y-m-d H:i:s',$last_date);
        // select all query
        $query = "SELECT t.id, t.time_finalised,ut.id AS user_trade_id, ut.balance_after_trade, ut.balance_after_trade_update, 
                    ut.balance_before_trade as user_balance_before_trade, t.balance_before_trade as trade_balance_before_trade  
                    FROM " . $this->table_trades . " t
                    LEFT JOIN ".$table_demo_trade." ut ON ut.trade_id = t.id
                    WHERE ut.user_id = ? AND t.trade_status != 0 AND t.type = 'trade' 
                    AND t.time_finalised <= '". $last_hy . "' 
                    ORDER BY t.time_finalised DESC LIMIT 0,1";
        // prepare query statement
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $account_id);
        // execute query
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        // set values to object properties
        $this->trade_id = $row['id'];
        $this->time_finalised = $row['time_finalised'];
        $this->user_trade_id = $row['user_trade_id'];
        $this->balance_after_trade_update = $row['balance_after_trade_update'];
        $this->balance_after_trade = $row['balance_after_trade'];
        $this->user_balance_before_trade = $row['user_balance_before_trade'];
        $this->trade_balance_before_trade = $row['trade_balance_before_trade'];
    }

    //Trường hợp không có trade của những ngày ở giữa
    function getListNotTradeYesterday($account_id, $time_finalised)
    {
        $date = date("Y-m-d ").' 00:00:00';
        // select all query
        $query = "SELECT trade_status, time_finalised, id, amount, type, profit_loss, UNIX_TIMESTAMP(time_finalised) AS timestamp, DAY(time_finalised) AS day_of_month
                    FROM " . $this->table_trades . "
                    WHERE created_by = ? AND trade_status != 0 AND (type = 'addfund' OR type = 'withdraw' )
                    AND time_finalised < '". date($date) ."' AND time_finalised > '". date($time_finalised). "' 
                    ORDER BY time_finalised DESC";
        // prepare query statement
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $account_id);
        // execute query
        $stmt->execute();
        return $stmt;
    }

    // function get last trade yesterday
    function getCapitalUser($account_id, $type = 'addmember', $is_demo = false)
    {
        if($is_demo == 'true') {
            $table_demo_trade = $this->dbprefix.'user_demo_trades';
        } else {
            $table_demo_trade = $this->dbprefix.'user_trades';
        }
        $sql = '';
        // Check get add fund and withdraw capital
        if ($type == "addfund" || $type == "withdraw") {
            $sql = "AND WithdrawCapital='capital'";
        }
        // select all query
        $query = "SELECT SUM(amount) as amount
                    FROM " . $this->table_trades . "
                    WHERE type = '".$type."' ".$sql." AND created_by = ?";
        // prepare query statement
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $account_id);
        // execute query
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        //neu so tien ban dau bang 0 thi kiem tra xem user do da tham gia trade nao hay khong
        if(empty($row['amount']) && $type == "addmember") {
            $query = "SELECT ut.balance_before_trade 
                        FROM " . $table_demo_trade . " ut
                        LEFT JOIN ".$this->table_trades." t ON t.id = ut.trade_id 
                        WHERE ut.user_id = ? AND t.trade_status != 0 AND t.type = 'trade' 
                        ORDER BY t.id ASC";
            // prepare query statement
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $account_id);
            // execute query
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
        }
        // set values to object properties
        $this->amount = $row['amount'];
    }

    // function get last trade yesterday
    function getProfitUser($account_id, $type, $created_at)
    {
        $sql = '';
        // Check get add fund and withdraw capital
        if ($type == "addfund" || $type == "withdraw") {
            $sql = "AND WithdrawCapital='profit' AND DATE(created_at) = '".$created_at."'";
        }
        // select all query
        $query = "SELECT SUM(amount) as amount
                    FROM " . $this->table_trades . "
                    WHERE type = '".$type."' ".$sql." AND created_by = ?";
        // prepare query statement
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $account_id);
        // execute query
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        // set values to object properties
        $this->amount = $row['amount'];
    }

    // function get balance after trade
    function getBalanceAfterTrade($account_id, $trade_id, $is_demo = false)
    {
        if($is_demo == 'true') {
            $table_demo_trade = $this->dbprefix.'user_demo_trades';
        } else {
            $table_demo_trade = $this->dbprefix.'user_trades';
        }
        // select all query
        $query = "SELECT t.amount
                    FROM " . $this->table_trades . " t
                    LEFT JOIN ".$table_demo_trade." ut ON ut.trade_id = t.id
                    WHERE ut.user_id = ? AND ut.trade_id = ?";
        // prepare query statement
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $account_id);
        $stmt->bindParam(2, $trade_id);
        // execute query
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        // set values to object properties
        $this->amount = $row['amount'];
    }

    // Function get total withdraw accounts
    function getTotalWithdrawAccounts($account_id)
    {
        // select all query
        $query = "SELECT SUM(profit_loss) as profit_loss
                    FROM " . $this->table_trades . "
                    WHERE type = 'withdraw' AND WithdrawCapital = 'profit' AND trade_status = 2 AND created_by = ? 
                    ORDER BY time_finalised ASC";
        // prepare query statement
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $account_id);
        // execute query
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        // set values to object properties
        $this->profit_loss = $row['profit_loss'];
    }

    // Function get list trade of pool
    function getTradesOfPool($account_id, $pool_id, $trades_month, $hightyield, $first_day_of_month, $last_day_of_month, $date, $offset, $is_demo = false)
    {
        if($is_demo == 'true') {
            $table_demo_trade = $this->dbprefix.'user_demo_trades';
        } else {
            $table_demo_trade = $this->dbprefix.'user_trades';
        }
        $sql = '';
        $limit = $offset * 10;
        // Get list trade of month
        if($trades_month == 'month-index') {
            $sql .= " AND t.time_finalised <= '".date('Y-m-d H:i:s', $last_day_of_month)."' AND t.time_finalised >= '".date('Y-m-d H:i:s', $first_day_of_month)."'";
            $sql .= " AND t.trade_status != 0 AND t.type = 'trade' ORDER BY t.time_finalised DESC";
        } elseif($trades_month == 'true') {
            if ($hightyield == 'true') {
                $last_hy = date('Y-m',mktime(0, 0, 0, date("m",strtotime($date)) - 1, 1, date("Y",strtotime($date))));
                $sql .= " AND DATE_FORMAT(t.time_finalised, '%Y-%m') <= '".$last_hy."'";
            } else {
                    $sql .= " AND t.time_finalised <= '".date('Y-m-d H:i:s', $last_day_of_month)."' AND t.time_finalised >= '".date('Y-m-d H:i:s', $first_day_of_month)."'";
                }
            $sql .= " AND t.trade_status != 0 AND t.type = 'trade' AND ut.block = 0 ORDER BY t.time_finalised DESC LIMIT $limit,10";
        } else {
            $sql .= " AND t.trade_status = 0 AND ut.block = 0 ORDER BY t.time_started DESC";
        }
        // select all query
        $query = "SELECT t.id, t.amount AS trade_amount, t.fee_percent, ut.amount_trade AS amount, t.profit_loss, t.content, t.change_percen, t.time_started, t.time_finalised, t.trade_status, t.reset_percen, 
                    t.link_score, t.content_result, t.game_time, t.balance_after_trade, t.balance_after_trade_update, 
                    t.balance_before_trade AS trade_balance_before_trade,
                    t.balance_before_trade_fixed AS balance_before_trade_fixed,
                    UNIX_TIMESTAMP(t.time_finalised) AS timestamp,
                    DAY(t.time_finalised) AS day_of_month,
                    ut.percen AS user_percen,
                    ut.balance_after_trade_update as user_balance_after_trade_update,
                    ut.balance_before_trade AS user_balance_before_trade,
                    ut.balance_before_trade_fixed AS user_balance_before_trade_fixed 
                    FROM " . $this->table_trades . " t
                    LEFT JOIN ".$table_demo_trade." ut ON ut.trade_id = t.id
                    WHERE ut.user_id = ? AND t.pool_id = ?" . $sql;
        // prepare query statement
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $account_id);
        $stmt->bindParam(2, $pool_id);
        // execute query
        $stmt->execute();
        return $stmt;
    }

    // function get last trade yesterday
    function getTotalItemsTrade($account_id, $finalised, $first_day_of_month, $last_day_of_month, $is_demo = false)
    {
        if($is_demo == 'true') {
            $table_demo_trade = $this->dbprefix.'user_demo_trades';
        } else {
            $table_demo_trade = $this->dbprefix.'user_trades';
        }
        $sql = '';
        // Check get add fund and withdraw capital
        if ($finalised == "true") {
            $sql .= " AND t.trade_status != 0 AND t.time_finalised <= '".date('Y-m-d H:i:s', $last_day_of_month)."' AND t.time_finalised >= '".date('Y-m-d H:i:s', $first_day_of_month)."'";
        } else {
            $sql .= " AND t.trade_status = 0";
        }

        $query = "SELECT COUNT(t.id) as total_items 
                    FROM " . $this->table_trades . " t
                    LEFT JOIN ".$table_demo_trade." ut ON ut.trade_id = t.id 
                    WHERE ut.user_id = ? AND t.type = 'trade'". $sql ."
                    ORDER BY t.time_finalised DESC";
        // prepare query statement
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(1, $account_id);
        // execute query
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        // set values to object properties
        $this->total_items = $row['total_items'];
    }

    //Trường hợp không có trade của những ngày ở giữa
    function getListNotTradeMonth($account_id, $first_day_of_month, $last_day_of_month)
    {
        // select all query
        $query = "SELECT trade_status, time_finalised, id, amount, type, profit_loss, UNIX_TIMESTAMP(time_finalised) AS timestamp, DAY(time_finalised) AS day_of_month
                    FROM " . $this->table_trades . "
                    WHERE created_by = ? AND trade_status != 0 AND (type = 'addfund' OR type = 'withdraw' ) 
                    AND time_finalised <= '".date('Y-m-d H:i:s', $last_day_of_month)."' AND time_finalised >= '".date('Y-m-d H:i:s', $first_day_of_month)."' 
                    ORDER BY time_finalised DESC";
        // prepare query statement
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $account_id);
        // execute query
        $stmt->execute();
        return $stmt;
    }

    // function get last trade yesterday
    function getFirstBalanceOfUser($account_id, $is_demo = false)
    {
        if($is_demo == 'true') {
            $table_demo_trade = $this->dbprefix.'user_demo_trades';
        } else {
            $table_demo_trade = $this->dbprefix.'user_trades';
        }
        // select all query
        $query = "SELECT t.id, t.trade_status, t.profit_loss, t.change_percen, ut.balance_after_trade_update, t.reset_percen,
                    t.amount AS trade_amount, t.fee_percent, ut.amount_trade AS amount, ut.percen AS user_percen, 
                    ut.balance_before_trade as user_balance_before_trade, t.balance_before_trade as trade_balance_before_trade  
                    FROM " . $this->table_trades . " t
                    LEFT JOIN ".$table_demo_trade." ut ON ut.trade_id = t.id 
                    WHERE ut.user_id = ? AND t.trade_status != 0 AND t.type = 'trade' 
                    ORDER BY t.time_finalised ASC LIMIT 0,1";
        // prepare query statement
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $account_id);
        // execute query
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        // set values to object properties
        $this->trade_id = $row['id'];
        $this->trade_status = $row['trade_status'];
        $this->profit_loss = $row['profit_loss'];
        $this->change_percen = $row['change_percen'];
        $this->balance_after_trade_update = $row['balance_after_trade_update'];
        $this->reset_percen = $row['reset_percen'];
        $this->trade_amount = $row['trade_amount'];
        $this->fee_percent = $row['fee_percent'];
        $this->amount = $row['amount'];
        $this->user_percen = $row['user_percen'];
        $this->user_balance_before_trade = $row['user_balance_before_trade'];
        $this->trade_balance_before_trade = $row['trade_balance_before_trade'];
    }

    //lay so ngay co addfund/withdraw hoac trade
    function getDateAddTrade($account_id, $first_day_of_month, $last_day_of_month, $is_demo = false)
    {
        if($is_demo == 'true') {
            $table_demo_trade = $this->dbprefix.'user_demo_trades';
        } else {
            $table_demo_trade = $this->dbprefix.'user_trades';
        }
        // select all query
        $query = "SELECT DATE_FORMAT(t.time_finalised,'%d') AS day_of_month 
                    FROM " . $this->table_trades . " t
                    LEFT JOIN ".$table_demo_trade." ut ON ut.trade_id = t.id 
                    WHERE t.trade_status > 0 AND ut.user_id = ? 
                    AND time_finalised <= '".date('Y-m-d H:i:s', $last_day_of_month)."' 
                    AND time_finalised >= '".date('Y-m-d H:i:s', $first_day_of_month)."' 
                    GROUP BY DATE(t.time_finalised) ORDER BY time_finalised DESC";
        // prepare query statement
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $account_id);
        // execute query
        $stmt->execute();
        return $stmt;
    }

    //Get infor addfund/withdraw in the day
    function getActionTradeByDate($account_id, $select_date)
    {
        // select all query
        $query = "SELECT content, trade_status, message, time_finalised, id, type, amount, profit_loss,
                    UNIX_TIMESTAMP(time_finalised) AS timestamp
                    FROM " . $this->table_trades . " 
                    WHERE type IN ('addfund', 'withdraw') AND created_by = ? AND DATE(time_finalised)= ? 
                    ORDER BY time_finalised DESC";
        // prepare query statement
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $account_id);
        $stmt->bindParam(2, $select_date);
        // execute query
        $stmt->execute();
        return $stmt;
    }

    // function get trade before day
    function getTradeBeforeDate($account_id, $time_selection)
    {
        // select all query
        $query = "SELECT t.id, t.trade_status, t.profit_loss, t.amount 
                    FROM " . $this->table_trades . " t
                    LEFT JOIN ".$this->dbprefix."user_trades ut ON ut.trade_id = t.id 
                    WHERE ut.user_id = ? AND t.time_finalised < ?";
        // prepare query statement
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $account_id);
        $stmt->bindParam(2, $time_selection);
        // execute query
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->id = $row['id'];
        $this->trade_status = $row['trade_status'];
        $this->profit_loss = $row['profit_loss'];
        $this->amount = $row['amount'];
    }

    // function get trade end in day
    function getTradeEndInDay($account_id, $first_day_of_month, $last_day_of_month, $is_demo = false)
    {
        if($is_demo == 'true') {
            $table_demo_trade = $this->dbprefix.'user_demo_trades';
        } else {
            $table_demo_trade = $this->dbprefix.'user_trades';
        }
        // select all query
        $query = "SELECT t.id, t.trade_status, t.profit_loss, t.amount 
                    FROM " . $this->table_trades . " t
                    LEFT JOIN ".$table_demo_trade." ut ON ut.trade_id = t.id 
                    WHERE ut.user_id = ?
                    AND t.time_finalised <= '".date('Y-m-d H:i:s', $last_day_of_month)."' AND t.time_finalised >= '".date('Y-m-d H:i:s', $first_day_of_month)."'";
        // prepare query statement
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $account_id);
        // execute query
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->id = $row['id'];
        $this->trade_status = $row['trade_status'];
        $this->profit_loss = $row['profit_loss'];
        $this->amount = $row['amount'];

    }

    // Function get all trade of month
    function getAllTradeOfMonth($account_id, $time_selection, $is_demo = false)
    {
        if($is_demo == 'true') {
            $table_demo_trade = $this->dbprefix.'user_demo_trades';
        } else {
            $table_demo_trade = $this->dbprefix.'user_trades';
        }
        // select all query
        $query = "SELECT t.id, t.type, t.profit_loss, t.trade_status, t.time_finalised, t.fee_percent, t.change_percen, t.reset_percen,
                    ut.amount_trade, t.balance_before_trade AS trade_balance_before_trade, ut.id AS ut_id, t.amount AS trade_amount,
                    ut.balance_after_trade_update AS balance_after_trade, ut.percen AS user_percen,
                    ut.balance_before_trade_fixed AS user_balance_before_trade_fixed, t.WithdrawCapital,
                    ut.balance_before_trade AS user_balance_before_trade 
                    FROM " . $this->table_trades . " t
                    LEFT JOIN ".$table_demo_trade." ut ON ut.trade_id = t.id
                    WHERE ut.user_id = ? AND t.trade_status != 0
                    AND date_format(t.time_finalised, '%Y-%m') = ?
                    ORDER BY t.time_finalised DESC, t.time_started ASC, id DESC";
        // prepare query statement
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $account_id);
        $stmt->bindParam(2, $time_selection);
        // execute query
        $stmt->execute();
        return $stmt;
    }

    // function get capital user demo
    function getCaptitalAccountDemo($account_id, $time_selection)
    {
        // select all query
        $query = "SELECT ut.balance_before_trade AS capital
                    FROM " . $this->table_trades . " t
                    LEFT JOIN ".$this->dbprefix."user_demo_trades ut ON ut.trade_id = t.id 
                    WHERE t.type = 'trade' AND ut.user_id = ? AND t.time_started >= '".date('Y-m-d H:i:s', $time_selection)."'
                    ORDER BY t.time_started ASC, t.id ASC LIMIT 0,1";
        // prepare query statement
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $account_id);
        // execute query
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->capital = $row['capital'];
    }

    // Function get list trade of pool
    function getTradesOfMonth($account_id, $pool_id, $first_day_of_month, $last_day_of_month, $is_demo = false)
    {
        if($is_demo == 'true') {
            $table_demo_trade = $this->dbprefix.'user_demo_trades';
        } else {
            $table_demo_trade = $this->dbprefix.'user_trades';
        }
        // select all query
        $query = "SELECT t.id, t.amount AS trade_amount, t.fee_percent, ut.amount_trade AS amount, t.profit_loss, t.content, t.time_started, t.time_finalised, t.trade_status, t.reset_percen, 
                    t.content_result, t.balance_after_trade, t.balance_after_trade_update, 
                    t.balance_before_trade AS trade_balance_before_trade,
                    t.balance_before_trade_fixed AS balance_before_trade_fixed,
                    UNIX_TIMESTAMP(t.time_finalised) AS timestamp,
                    DAY(t.time_finalised) AS day_of_month,
                    ut.balance_after_trade_update as user_balance_after_trade_update,
                    ut.balance_after_trade AS user_balance_after_trade,
                    ut.balance_before_trade AS user_balance_before_trade,
                    ut.balance_before_trade_fixed AS user_balance_before_trade_fixed 
                    FROM " . $this->table_trades . " t
                    LEFT JOIN ".$table_demo_trade." ut ON ut.trade_id = t.id
                    WHERE ut.user_id = ? AND t.pool_id = ? AND t.trade_status != 0 AND t.type = 'trade'
                    AND t.time_finalised <= '".date('Y-m-d H:i:s', $last_day_of_month)."' 
                    AND t.time_finalised >= '".date('Y-m-d H:i:s', $first_day_of_month)."'
                    ORDER BY t.time_finalised DESC";
        // prepare query statement
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $account_id);
        $stmt->bindParam(2, $pool_id);
        // execute query
        $stmt->execute();
        return $stmt;
    }

    // function get latest trade
    function getLastestTrade()
    {
        // select all query
        $query = "SELECT t.id, t.time_finalised
                    FROM " . $this->table_trades . " t
                    WHERE t.type = 'trade' AND t.trade_status != 0
                    ORDER BY t.time_finalised DESC, t.id ASC LIMIT 0,1";
        // prepare query statement
        $stmt = $this->conn->prepare($query);
        // execute query
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->id = $row['id'];
        $this->time_finalised = $row['time_finalised'];
    }
  
      //Get list pool
    public function getAllTrade($time_started, $time_finalised)
    {
        $sql = '';
        $order_by = '';
        // if(isset($time_started)) {
        //     $sql = "t.time_started >= '".date('Y-m-d H:i:s', $time_started)."' AND ";
        //     $order_by = "ORDER BY t.time_started ASC";
        // }
        // if(isset($time_finalised)) {
        //     $sql = "t.time_finalised >= '".date('Y-m-d H:i:s', $time_finalised)."' AND";
        //     $order_by = "ORDER BY t.time_finalised ASC";
        // }
        $query = 'SELECT a.trade_id, a.content AS content, f.content AS content_result,1 AS pool_id, a.time AS time_started, f.time AS time_finalised, a.amount AS amount, f.amount AS profit_loss, f.fee AS fee, f.status AS trade_status, a.link_score AS link_score, a.games_id AS game, a.fund_in_trades AS fund_in_trades_add, f.fund_in_trades AS fund_in_trades_finalised, a.total_balance AS total_balance_add, f.total_balance AS total_balance_finalsed, a.available_balance AS available_balance_add, f.available_balance AS available_balance_finalised 
                FROM (SELECT * FROM `k327w_transactions_trades` WHERE type = 0) AS a LEFT JOIN (SELECT * FROM `k327w_transactions_trades` WHERE type = 1) AS f ON a.trade_id = f.trade_id 
                ORDER BY `a`.`time` DESC';
        //prepare query statement
        $stmt = $this->conn->prepare($query);
        // bind id of product  to be updated
        $stmt->execute();
        return $stmt;
    }
}
?>