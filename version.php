<?php
echo "Versão do código: " . date('Y-m-d H:i:s') . "<br>";
echo "Arquivo atualizado em: " . date('Y-m-d H:i:s', filemtime(__FILE__)) . "<br>";
echo "AuthController existe: " . (class_exists('App\\Controllers\\AuthController') ? 'SIM' : 'NÃO') . "<br>";