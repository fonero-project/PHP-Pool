<?php

ini_set('display_errors',1);
error_reporting(E_ALL);

$FORCE =isset($argv[1]) && $argv[1] == 'FORCE';

$runOnHours = array('1','4','7','10','13','16','19','22');
if (!$FORCE && array_search(date('G'),$runOnHours) === false) {
	echo "NOT TIME TO RUN ".date('G')."\n";
	exit;
}

require_once(dirname(__FILE__).'/../../bootstrap.php');

echo "############## UPDATEOWED ".date("Y.m.d H.i.s")."\n";

$settingsDao = new GrcPool_Settings_DAO();
if (!$FORCE && $settingsDao->getValueWithName(Constants::SETTINGS_GRC_CLIENT_ONLINE) != '1') {
	echo "GRC CLIENT OFFLINE\n\n";
	exit;
}

$fp = fopen(Constants::PAYOUT_LOCK_FILE,"w");
if (!flock($fp, LOCK_EX | LOCK_NB)) {
	echo('CRITICAL: !!!!!!!!!!!! LOCKED !!!!!!!!!!!!!');
	exit;
}

$walletDao = new GrcPool_Wallet_Basis_DAO();
$settingsDao = new GrcPool_Settings_DAO();

$minStakeBalance = $settingsDao->getValueWithName(Constants::SETTINGS_MIN_STAKE_BALANCE)*COIN;
echo "Min Stake Balance: ".($minStakeBalance/COIN)."\n";

$hostCreditDao = new GrcPool_Member_Host_Credit_DAO();
$hostDao = new GrcPool_Member_Host_DAO();

$daemon = GrcPool_Utils::getDaemonForPool();

$basisObj = $walletDao->initWithKey(1);
$WALLETBASIS = $basisObj->getBasis();
echo "Wallet Basis: ".($WALLETBASIS/COIN)."\n";
$totalBalance = $daemon->getTotalBalance()*COIN;
echo "Current Balance: ".($totalBalance/COIN)."\n";
$totalInterest = $daemon->getTotalInterest()*COIN;
echo "Wallet Interest: ".($totalInterest/COIN)."\n";
$totalBalance = $totalBalance-$totalInterest;
echo "Available Balance: ".($totalBalance/COIN)."\n";

if ($totalBalance < $WALLETBASIS) {
	echo ($totalBalance/COIN) .' to low < '.($WALLETBASIS/COIN)."\n";
	exit;
}

$totalOwed = $hostCreditDao->getTotalOwed()*COIN;
echo "Total Owed: ".($totalOwed/COIN)."\n";

$stakeBalance = $totalBalance - $WALLETBASIS - $totalOwed;
echo 'Stake Balance: '.($stakeBalance/COIN)." = ".($totalBalance/COIN)." - ".($WALLETBASIS/COIN)." - ".($totalOwed/COIN)."\n";

if ($totalBalance - $stakeBalance < $WALLETBASIS) {
	echo 'Funds Too Low: '."\n";
	exit;
}

$totalMag = $hostCreditDao->getTotalMag();
echo 'Total Mag: '.$totalMag."\n";

if ($stakeBalance < $minStakeBalance) {
	echo 'not enough stake balance  '.($minStakeBalance/COIN).'  >  '.number_format($stakeBalance/COIN,8)."\n";
	exit;
}

$sql = 'update '.Constants::DATABASE_NAME.'.member_host_credit set '.Constants::DATABASE_NAME.'.member_host_credit.owed = '.Constants::DATABASE_NAME.'.member_host_credit.owed + (('.Constants::DATABASE_NAME.'.member_host_credit.mag/'.$totalMag.') * '.($stakeBalance/COIN).'),
		'.Constants::DATABASE_NAME.'.member_host_credit.owedCalc = concat('.Constants::DATABASE_NAME.'.member_host_credit.owedCalc,\'+((\','.Constants::DATABASE_NAME.'.member_host_credit.mag,\'/\','.$totalMag.',\')*\','.($stakeBalance/COIN).',\')\') where mag > 0';
echo "\n\n".$sql."\n\n";
$hostCreditDao->executeQuery($sql);

// cleanup rows with a long owedCalc
$sql = 'update '.Constants::DATABASE_NAME.'.member_host_credit set owedCalc = concat(\'+\',owed) where char_length(owedCalc) > 500';
$hostDao->executeQuery($sql);