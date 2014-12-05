<?php
use yii\db\Migration;

class m130411_043847_process_job extends Migration
{
	public function up()
	{
        $this->createTable(
            'process_jobs',
            array(
                'id' => 'pk',

                'pid' => 'int',
                'uid' => 'varchar(512)',

                'start' => 'int',
                'end' => 'int',

                'name' => 'varchar(512)',
                'action' => 'varchar(512)',

                'encode_params' => 'text',
                'error' => 'text',

                'progress' => 'int not null default 0',
                'status' => 'int not null default 0',

                'result' => 'text'
            ),
            ''
        );
	}

	public function down()
    {
        $this->dropTable('process_jobs');
	}

	/*
	// Use safeUp/safeDown to do migration with transaction
	public function safeUp()
	{
	}

	public function safeDown()
	{
	}
	*/
}
