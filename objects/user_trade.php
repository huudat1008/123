<?php
include_once '../config/core.php';
class UserTrade{
  
    // database connection and table name
    private $conn;
    private $table_name = "user_trades";
  
    // object properties
    public $id;
    public $amount;
    public $t_balance_before_trade;
    public $change_percen;
    public $reset_percen;
    public $block;
    public $user_percen;
    public $u_balance_before_trade;
  
    // constructor with $db as database connection
    public function __construct($db){
      	date_default_timezone_set('UTC');
        $this->conn = $db;
        // Get api config
        $database = new ApiConfig();
        $this->table_trade = $database->dbprefix.$this->table_name;
        $this->dbprefix = $database->dbprefix;
    }

    // Function get list user trade by account id
    function getUserTrade($user_id, $is_demo = false)
    {
        if($is_demo == 'true') {
            $this->table_trade = $this->dbprefix.'user_demo_trades';
        }
        // select all query
        $query = "SELECT t.id, t.balance_before_trade AS t_balance_before_trade, t.amount, t.change_percen, t.reset_percen, ut.amount_trade, ut.block, ut.percen AS user_percen, ut.balance_before_trade AS u_balance_before_trade 
                    FROM " . $this->table_trade . " ut
                    LEFT JOIN ".$this->dbprefix."trades t ON t.id = ut.trade_id 
                    WHERE ut.user_id = ? AND t.trade_status = 0 AND ut.block = 0 AND t.time_finalised = '0000-00-00 00:00:00'";
        // prepare query statement
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $user_id);
        // execute query
        $stmt->execute();
        return $stmt;
    }

    function getUserTradeDeactive($trade_id)
    {
        // select all query
        $query = "SELECT ut.* 
                    FROM " . $this->table_trade . " ut
                    LEFT JOIN ".$this->dbprefix."accounts a ON a.id = ut.user_id
                    LEFT JOIN ".$this->dbprefix."account_groups ag ON ag.id = a.groups
                    WHERE ut.trade_id = ? AND ut.block = 1 AND ag.group_type = 2";
        // prepare query statement
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $trade_id);
        // execute query
        $stmt->execute();
        return $stmt;
    }

    // Function get user trade principal by account id
    function getUserTradePrincipal($account_id, $is_demo = false)
    {
        if($is_demo == 'true') {
            $this->table_trade = $this->dbprefix.'user_demo_trades';
        }
        // select all query
        $query = "SELECT ut.balance_before_trade AS amount, t.type, t.time_finalised, t.time_started 
                    FROM " . $this->table_trade . " ut
                    LEFT JOIN ".$this->dbprefix."trades t ON t.id = ut.trade_id 
                    WHERE ut.user_id = ? ORDER BY ut.id ASC";
        // prepare query statement
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $account_id);
        // execute query
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        // set values to object properties
        $this->amount = $row['amount'];
        $this->type = $row['type'];
        $this->time_started = $row['time_started'];
        $this->time_finalised = $row['time_finalised'];
    }

    // Function get list user trade by account id
    function getUserTradeOfMonth($pool_id, $first_day_of_month)
    {
        // select all query
        $query = "SELECT t.profit_loss, t.time_finalised, t.change_percen, t.reset_percen, ut.amount_trade, t.trade_status, ut.percen AS user_percen, 
                    t.balance_before_trade AS trade_balance_before_trade,
                    ut.balance_before_trade AS user_balance_before_trade,
                    (DAY(t.time_finalised) + 0.5) AS day_of_month 
                    FROM " . $this->table_trade . " ut
                    LEFT JOIN ".$this->dbprefix."trades t ON t.id = ut.trade_id 
                    WHERE t.type = 'trade' AND t.pool_id = ? AND t.trade_status > 0 AND t.time_finalised != '0000-00-00 00:00:00'
                    AND t.time_started >= '".date('Y-m-d H:i:s', $first_day_of_month)."'
                    ORDER BY t.time_finalised ASC";
        // prepare query statement
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $pool_id);
        // execute query
        $stmt->execute();
        return $stmt;
    }

    // Function get total account on trade
    function getTotalAccountOnTrade($trade_id)
    {
        // select all query
        $query = "SELECT id
                    FROM " . $this->table_trade . "
                    WHERE trade_id = ?";
        // prepare query statement
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $trade_id);
        // execute query
        $stmt->execute();
        return $stmt;
    }

    // Function get list user trade by account id
    function getAllUserTrade($user_id, $is_demo = false)
    {
        if($is_demo == 'true') {
            $this->table_trade = $this->dbprefix.'user_demo_trades';
        }
        // select all query
        $query = "SELECT t.id, t.amount, t.change_percen, t.reset_percen, ut.amount_trade, t.fee_percent, t.profit_loss,
                    t.balance_before_trade AS trade_balance_before_trade,  ut.balance_before_trade AS user_balance_before_trade 
                    FROM " . $this->table_trade . " ut
                    LEFT JOIN ".$this->dbprefix."trades t ON t.id = ut.trade_id 
                    WHERE ut.user_id = ? AND t.trade_status != 0 AND t.type = 'trade'
                    ORDER BY t.time_finalised ASC";
        // prepare query statement
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $user_id);
        // execute query
        $stmt->execute();
        return $stmt;
    }
}
?>