<?php if(!defined('HVZ')) die(-1); ?>
<h1>Print ID</h1>
<?php
if(!$user->registered) {
    ?><span class="error">You must be registered for the game to print your ID</span><?php
} else {
    //calculate token bits
    $tokenbits = array(
        /* 0 => */ $user->getName(),
        /* 1 => */ $user->getId(),
		/* 2 => */ $user->getFactionName(),
        /* 3 => */ $user->getUin(),
        /* 4 => */ md5($secretsalt . $user->getName() . $user->getId() . $user->getFactionName() . $user->getUin())
    );
    $token = rawurlencode(encodeString(implode('|', $tokenbits)));
?>
<div>
Use your browser's print feature in order to print the id, then cut it out and carry it with you.
If you get tagged, give the id to the zombie that tagged you.
</div>
<br />
<img src="genid.php?token=<?= $token ?>" alt="ID image" />
<?php
}
