<?php
include_once '../config/core.php';
class Account
{
    // database connect and table name
    private $conn;
    private $table_name = "accounts";

    // object properties
    public $id;
    public $user_id;
    public $pool_id;
    public $groups;
    public $currentBalance;
    public $joiningDate;

    // constructor with $db as database connection
    public function __construct($db)
    {
      	date_default_timezone_set('UTC');
        $this->conn = $db;
        $database = new ApiConfig();
        $this->table_accounts = $database->dbprefix.$this->table_name;
        $this->dbprefix = $database->dbprefix;
    }

    //Get Infor account by account id
    public function getInfoAccountById($account_id)
    {
        $query = "SELECT * FROM " . $this->table_accounts . " WHERE id = ?";
        //prepare query statement
        $stmt = $this->conn->prepare($query);
        // bind id of product  to be updated
        $stmt->bindParam(1, $account_id);
        $stmt->execute();
        return $stmt;
    }
    
    // get account by pool
    public function getAccountByUserPool($user_id, $domain_name)
    {
        $query = "SELECT a.*, u.name as full_name, u.username, u.email FROM " . $this->table_accounts . " a 
            LEFT JOIN " . $this->dbprefix . "users u ON u.id = a.user_id
            LEFT JOIN " . $this->dbprefix . "pools p ON p.id = a.pool_id
            LEFT JOIN " . $this->dbprefix . "account_groups ag ON ag.id = a.groups
            WHERE a.user_id = ? AND p.link = ? AND (ag.group_type = 2 OR ag.group_type = 1) LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $user_id);
        $stmt->bindParam(2, $domain_name);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        // set values to object properties
        $this->account_id = $row['id'];
        $this->user_id = $row['user_id'];
        $this->pool_id = $row['pool_id'];
        $this->name = $row['name'];
        $this->full_name = $row['full_name'];
        $this->username = $row['username'];
        $this->email = $row['email'];
        $this->groups = $row['groups'];
        $this->currentBalance = $row['currentBalance'];
        $this->joiningDate = $row['joiningDate'];
        $this->expiredTime = $row['expiredTime'];
        $this->lastvisitDate = $row['lastvisitDate'];
        $this->isChanged = $row['isChanged'];
        $this->displayName = $row['displayName'];
        $this->subcribemail = $row['subcribemail'];
        $this->subcribetype = $row['subcribetype'];
    }

    // Get current balance of account
    function getBalance($id) 
    {
        $query = "SELECT currentBalance FROM " . $this->table_accounts . " WHERE id = ? LIMIT 0,1";
        //prepare query statement
        $stmt = $this->conn->prepare($query);
        // bind id of product  to be updated
        $stmt->bindParam(1, $id);
        $stmt->execute();
        // get retrieved now
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        // set value to object properties
        $this->currentBalance = $row['currentBalance'];
    }

    // Get current balance of account
    function getUserByUserName($username) 
    {
        $query = "SELECT id, password FROM " . $this->dbprefix . "users WHERE username = ? LIMIT 0,1";
        //prepare query statement
        $stmt = $this->conn->prepare($query);
        // bind id of product  to be updated
        $stmt->bindParam(1, $username);
        $stmt->execute();
        // get retrieved now
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        // set value to object properties
        $this->id = $row['id'];
        $this->password = $row['password'];
    }

    //Get Infor account by account id
    public function getInfoUserById($user_id)
    {
        $query = "SELECT u.*, um.group_id, ug.title as group_title FROM " . $this->dbprefix . "users u
                LEFT JOIN " . $this->dbprefix . "user_usergroup_map um ON um.user_id = u.id
                LEFT JOIN " . $this->dbprefix . "usergroups ug ON ug.id = um.group_id
                WHERE u.id = ?";
        //prepare query statement
        $stmt = $this->conn->prepare($query);
        // bind id of product  to be updated
        $stmt->bindParam(1, $user_id);
        $stmt->execute();
        return $stmt;
    }

    //Get Infor group by account id
    public function getGroupById($id_group)
	{
        $query = "SELECT * FROM " . $this->dbprefix . "account_groups WHERE id = ?";
        //prepare query statement
        $stmt = $this->conn->prepare($query);
        // bind id of product  to be updated
        $stmt->bindParam(1, $id_group);
        $stmt->execute();
        return $stmt;
    }

    //Update Infor account group
    public function updateAccount()
	{
        $query = "UPDATE " . $this->dbprefix . "accounts 
                    SET displayName = :displayName, subcribemail = :subcribemail, subcribetype = :subcribetype
                    WHERE id = :id";
        //prepare query statement
        $stmt = $this->conn->prepare($query);
        // sanitize
        $this->displayName = htmlspecialchars(strip_tags($this->displayName));
        $this->subcribemail = htmlspecialchars(strip_tags($this->subcribemail));
        $this->subcribetype = htmlspecialchars(strip_tags($this->subcribetype));
        // bind id of product  to be updated
        $stmt->bindParam(':displayName', $this->displayName);
        $stmt->bindParam(':subcribemail', $this->subcribemail);
        $stmt->bindParam(':subcribetype', $this->subcribetype);
        $stmt->bindParam(':id', $this->id);
        // execute the query
        if($stmt->execute()){
            return true;
        }
      
        return false;
    }

    //Update Infor kunena users
    public function updateKunenaUsers()
	{
        $query = "UPDATE " . $this->dbprefix . "kunena_users 
                    SET avatar = :avatar WHERE userid = :userid";
        //prepare query statement
        $stmt = $this->conn->prepare($query);
        // sanitize
        $this->avatar = htmlspecialchars(strip_tags($this->avatar));
        // bind id of product  to be updated
        $stmt->bindParam(':avatar', $this->avatar);
        $stmt->bindParam(':userid', $this->userid);
        // execute the query
        if($stmt->execute()){
            return true;
        }
      
        return false;
    }

    //Update Infor users table
    public function updateUsers()
	{
        $query = "UPDATE " . $this->dbprefix . "users 
                    SET email = :email WHERE id = :id";
        //prepare query statement
        $stmt = $this->conn->prepare($query);
        // sanitize
        $this->email = htmlspecialchars(strip_tags($this->email));
        // bind id of product  to be updated
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':id', $this->id);
        // execute the query
        if($stmt->execute()){
            return true;
        }
        return false;
    }

    //Update activation users table
    public function updateActivationUsers()
	{
        $query = "UPDATE " . $this->dbprefix . "users 
                    SET activation = :activation WHERE id = :id";
        //prepare query statement
        $stmt = $this->conn->prepare($query);
        // sanitize
        $this->activation = htmlspecialchars(strip_tags($this->activation));
        // bind id of product  to be updated
        $stmt->bindParam(':activation', $this->activation);
        $stmt->bindParam(':id', $this->id);
        // execute the query
        if($stmt->execute()){
            return true;
        }
        return false;
    }

    //Update visit date users table
    public function updateVisitDateUsers()
	{
        $query = "UPDATE " . $this->dbprefix . "users 
                    SET lastvisitDate = :lastvisitDate WHERE id = :id";
        //prepare query statement
        $stmt = $this->conn->prepare($query);
        // sanitize
        $this->lastvisitDate = htmlspecialchars(strip_tags($this->lastvisitDate));
        // bind id of product  to be updated
        $stmt->bindParam(':lastvisitDate', $this->lastvisitDate);
        $stmt->bindParam(':id', $this->id);
        // execute the query
        if($stmt->execute()){
            return true;
        }
        return false;
    }

    //Update visit date users table
    public function updateVisitDateAccounts()
	{
        $query = "UPDATE " . $this->table_accounts . " 
                    SET lastvisitDate = :lastvisitDate WHERE id = :id";
        //prepare query statement
        $stmt = $this->conn->prepare($query);
        // sanitize
        $this->lastvisitDate = htmlspecialchars(strip_tags($this->lastvisitDate));
        // bind id of product  to be updated
        $stmt->bindParam(':lastvisitDate', $this->lastvisitDate);
        $stmt->bindParam(':id', $this->id);
        // execute the query
        if($stmt->execute()){
            return true;
        }
        return false;
    }

    //Update Infor users table
    public function updatePassUser()
	{
        $query = "UPDATE " . $this->dbprefix . "users 
                    SET password = :password, activation = '' WHERE id = :id";
        //prepare query statement
        $stmt = $this->conn->prepare($query);
        // sanitize
        $this->password = htmlspecialchars(strip_tags($this->password));
        // bind id of product  to be updated
        $stmt->bindParam(':password', $this->password);
        $stmt->bindParam(':id', $this->id);
        // execute the query
        if($stmt->execute()){
            return true;
        }
      
        return false;
    }

    //Get Infor group by account id
    public function getKunenaUsers($user_id)
	{
        $query = "SELECT userid, status, avatar FROM " . $this->dbprefix . "kunena_users WHERE userid = ?";
        //prepare query statement
        $stmt = $this->conn->prepare($query);
        // bind id of product  to be updated
        $stmt->bindParam(1, $user_id);
        $stmt->execute();
        return $stmt;
    }

    //Update Infor logs table
    public function insertTableLogs()
	{
        $query = "INSERT INTO " . $this->dbprefix . "logs 
                    SET created_by = :created_by, created_at = :created_at, message = :message";
        //prepare query statement
        $stmt = $this->conn->prepare($query);
        // sanitize
        $this->created_by = htmlspecialchars(strip_tags($this->created_by));
        $this->created_at = htmlspecialchars(strip_tags($this->created_at));
        $this->message = htmlspecialchars(strip_tags($this->message));
        // bind id of product  to be updated
        $stmt->bindParam(':created_by', $this->created_by);
        $stmt->bindParam(':created_at', $this->created_at);
        $stmt->bindParam(':message', $this->message);
        // execute the query
        if($stmt->execute()){
            return true;
        }
      
        return false;
    }

     // Get user id by email
     function getUserIdByEmail($email) 
     {
        $query = "SELECT id FROM " . $this->dbprefix . "users WHERE email = ?";
        //prepare query statement
        $stmt = $this->conn->prepare($query);
        // bind id of product  to be updated
        $stmt->bindParam(1, $email);
        $stmt->execute();

        return $stmt;
    }

    // Get current balance of account
    function getInforUserByUserName($username) 
    {
        $query = "SELECT id, activation, block FROM " . $this->dbprefix . "users WHERE username = ? LIMIT 0,1";
        //prepare query statement
        $stmt = $this->conn->prepare($query);
        // bind id of product  to be updated
        $stmt->bindParam(1, $username);
        $stmt->execute();
        // get retrieved now
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        // set value to object properties
        $this->id = $row['id'];
        $this->activation = $row['activation'];
        $this->block = $row['block'];
    }

    //Get Infor group by account id
    public function getDataLogAction($key)
	{
        $query = "SELECT * FROM " . $this->dbprefix . "log_action WHERE session_id = ? 
                 AND action != 'Logout' order by id desc limit 1";
        //prepare query statement
        $stmt = $this->conn->prepare($query);
        // bind id of product  to be updated
        $stmt->bindParam(1, $key);
        $stmt->execute();
        return $stmt;
    }

    //Update Infor logs table
    public function insertTableLogAction()
	{
        $query = "INSERT INTO " . $this->dbprefix . "log_action 
                    SET session_id = :session_id, user_id = :user_id, account_id = :account_id, ip = :ip,
                    city = :city, country = :country,  action = :action,
                    timestart = :timestart, timelast = :timelast,  counter = :counter,
                    browser = :browser, timezone = :timezone,  lasturl = :lasturl";
        //prepare query statement
        $stmt = $this->conn->prepare($query);
        // sanitize
        $this->session_id = htmlspecialchars(strip_tags($this->session_id));
        $this->user_id = htmlspecialchars(strip_tags($this->user_id));
        $this->account_id = htmlspecialchars(strip_tags($this->account_id));
        $this->ip = htmlspecialchars(strip_tags($this->ip));
        $this->city = htmlspecialchars(strip_tags($this->city));
        $this->country = htmlspecialchars(strip_tags($this->country));
        $this->action = htmlspecialchars(strip_tags($this->action));
        $this->timestart = htmlspecialchars(strip_tags($this->timestart));
        $this->timelast = htmlspecialchars(strip_tags($this->timelast));
        $this->counter = htmlspecialchars(strip_tags($this->counter));
        $this->browser = htmlspecialchars(strip_tags($this->browser));
        $this->timezone = htmlspecialchars(strip_tags($this->timezone));
        $this->lasturl = htmlspecialchars(strip_tags($this->lasturl));
        // bind id of product  to be updated
        $stmt->bindParam(':session_id', $this->session_id);
        $stmt->bindParam(':user_id', $this->user_id);
        $stmt->bindParam(':account_id', $this->account_id);
        $stmt->bindParam(':ip', $this->ip);
        $stmt->bindParam(':city', $this->city);
        $stmt->bindParam(':country', $this->country);
        $stmt->bindParam(':action', $this->action);
        $stmt->bindParam(':timestart', $this->timestart);
        $stmt->bindParam(':timelast', $this->timelast);
        $stmt->bindParam(':counter', $this->counter);
        $stmt->bindParam(':browser', $this->browser);
        $stmt->bindParam(':timezone', $this->timezone);
        $stmt->bindParam(':lasturl', $this->lasturl);
        // execute the query
        if($stmt->execute()){
            return true;
        }
      
        return false;
    }
  
      //Get list pool
    public function getAllPool()
	{
        $query = "SELECT * FROM " . $this->dbprefix . "pools";
        //prepare query statement
        $stmt = $this->conn->prepare($query);
        // bind id of product  to be updated
        $stmt->bindParam(1, $key);
        $stmt->execute();
        return $stmt;
    }
    //Update isChanged accounts table
    public function updateIsChanged()
	{
        $query = "UPDATE " . $this->dbprefix . "accounts 
                    SET isChanged = 0 WHERE id = :id";
        //prepare query statement
        $stmt = $this->conn->prepare($query);
        // bind id of product  to be updated
        $stmt->bindParam(':id', $this->id);
        // execute the query
        if($stmt->execute()){
            return true;
        }
        return false;
    }
}