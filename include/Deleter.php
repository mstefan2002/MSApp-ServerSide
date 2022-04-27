<?php
class Deleter
{
    /**
     * Delete all the data associated with the email
     *
     * @param Database $db
     * @param string $email
     * 
     * @return void
     * 
     */
    public static function all(Database $db, string $email) : void
    {
        $db->start_transaction();
        self::user($db,$email);
        self::emailVerification($db,$email);
        self::session($db,$email);
        $db->end_transaction();
    }
    /**
     * Delete the email from email verification table
     *
     * @param Database $db
     * @param string $email
     * 
     * @return void
     * 
     */
    public static function emailVerification(Database $db, string $email) : void
    {
        EmailVerify::remove($db,$email);
    }
    /**
     * Delete the email from accounts table
     *
     * @param Database $db
     * @param string $email
     * 
     * @return void
     * 
     */
    public static function user(Database $db, string $email) : void
    {
        $user = new User($db,$email);
        $user->remove();
        unset($user);
    }

     /**
     * Delete the email from session table
     *
     * @param Database $db
     * @param string $email
     * 
     * @return void
     * 
     */
    public static function session(Database $db, string $email) : void
    {
        Session::remove($db,$email);
    }
}
?>