<?php
$passwordHash = password_hash("NuevaContraseña", PASSWORD_BCRYPT);
echo "Hash generado: " . $passwordHash;
