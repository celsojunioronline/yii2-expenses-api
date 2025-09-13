<?php

use yii\db\Migration;
use yii\helpers\Console;
use app\models\User;

class m250912_122017_seed_initial_data extends Migration
{
    public function safeUp()
    {
        $admin = new User();
        $admin->name = 'Administrador';
        $admin->email = 'admin@teste.com';
        $admin->setPassword('admin123');
        $admin->generateAuthKey();
        $admin->role = 'admin';
        $admin->save(false);
        Console::output("✅ Usuário admin criado: admin@teste.com / admin123");

        $user = new User();
        $user->name = 'Usuário Demo';
        $user->email = 'demo@teste.com';
        $user->setPassword('123456');
        $user->generateAuthKey();
        $user->role = 'user';
        $user->save(false);
        Console::output("✅ Usuário demo criado: demo@teste.com / 123456");
    }

    public function safeDown()
    {
        $this->delete('{{%users}}', ['email' => ['demo@teste.com', 'admin@teste.com']]);
    }
}
