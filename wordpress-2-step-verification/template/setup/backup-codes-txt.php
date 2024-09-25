<?php
/**
 * @var $user_login
 * @var $codes
 */
$file='backup-codes-'.$user_login.'.txt';
header("Content-Disposition: attachment; filename=" . urlencode($file));
header("Content-Type: application/octet-stream");
_e('BACKUP VERIFICATION CODES','wordpress-2-step-verification');
echo "\r\n";
echo $user_login;
echo "\r\n\r\n";

$codes=array_keys(array_filter($codes));

for($i=0;$i<5;$i++) {

    $line =($i+1).'. '. substr_replace($codes[$i],' ',4,0);
    if(isset($codes[$i+5])){
        $space=$i<4?' ':'';
        $line.="\t".$space.($i+6).'. '.substr_replace($codes[$i+5],' ',4,0);
    }
    echo $line."\r\n";
}
echo "\r\n";
_e('Date:','wordpress-2-step-verification');
echo mysql2date(get_option( 'date_format' ).''.get_option('time_format'),current_time('mysql'));
echo "\r\n\r\n";
_e('Running out of backup codes? Generate new ones:');echo "\r\n";
echo admin_url('profile.php?page=wp2sv'); echo "\r\n";
_e('Only the latest set of backup codes will work.','wordpress-2-step-verification');
echo "\r\n\r\n";
_e('Keep them someplace accessible, like your wallet.','wordpress-2-step-verification');echo "\r\n";
_e('Each code can be used only once.','wordpress-2-step-verification');
