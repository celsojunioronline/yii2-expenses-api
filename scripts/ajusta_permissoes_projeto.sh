#/bin/bash

echo "Ajustando permissões dos diretórios Yii2"

# Pastas de cache e runtime do Yii2
chmod -R 777 runtime/
chmod -R 777 web/assets/

echo "Permissões ajustadas com sucesso!"
