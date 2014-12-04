Job
===============

Xtlan\Job\Component\Controller\CreateBehavior - поведение для работы управляющего контроллера 
Xtlan\Job\Component\Console\WorkerController - абстрактный класс для консольной комманды, выполняющей роль исполниителя задачи
Xtlan\Job\Model\Job - модель Job для наследования (self::UID)
Xtlan\Job\Model\FileJob - модель FileJob для наследования 

Xtlan\Job\Component\Enum\JobStatus - JobStatus для enum


Инсталяция
==================

composer require xtlan\job:dev-master
./yii migrate --migrationPath=vendor\xtlan\job\migrations

создаем контроллер на основе поведения для управления
создаем комманду, расширяя класс
создаем модельку для связи

