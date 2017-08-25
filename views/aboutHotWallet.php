<?php
$webPage->appendTitle('Staking Wallet');
$webPage->append('
	<div class="panel panel-default">
		<div class="panel-heading">
   			<h3 class="panel-title"><i class="fa fa-fire"></i> Pool Staking Wallet</h3>
		</div>
		<div class="panel-body">
			The pool\'s staking wallet address
			<ul>
				<a href="'.GrcPool_Utils::getGrcAddressUrl($this->view->hotWallet).'">'.$this->view->hotWallet.'</a> <i class="fa fa-external-link"></i></li>
			</ul> 
			This wallet maintains the magnitude, receives the proof of research awards, and interest payments.
			Also the wallet handles the payouts to pool members. 
			<br/><br/>
			If you send coins to the staking wallet, you will effectively &quot;rain&quot; coins on the hosts in the pool.
			This is because payout amount is determined based on the <a href="/about/calculations">wallet balance, minus the basis and interest</a>.
			Also any project rain received into the staking wallet will be distributed to the hosts in the pool regardless of the project they are crunching on.
		</div>
	</div>
');		