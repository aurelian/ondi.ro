<?php
require 'ondi.ro.php';

$r= new OndiRequest(YOUR_API_KEY);
print $r->locate('Ploiesti');

print "----\n";

print $r->where('La limita dintre est si vest se gaseste Judetul Bistrița chiar cand credeam ca nu mai e nimic. Apoi am încercat și în Buzău cel vechi și neliniștit.', 'json');

