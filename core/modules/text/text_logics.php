<?php

require_once 'tasks/task2.php';
require_once 'tasks/task20.php';


class TextLogic
{
	public function executeAndShow()
	{

		if (isset($_GET['preset']) && $_GET['preset'] == 1) {
			$preset = include 'presets/preset_1.php';
		}
		if (isset($_GET['preset']) && $_GET['preset'] == 2) {
			$preset = include 'presets/preset_2.php';
		}
		if (isset($_GET['preset']) && $_GET['preset'] == 3) {
			$preset = include 'presets/preset_3.php';
		}
		if (isset($_GET['preset']) && $_GET['preset'] == 4) {
			$preset = include 'presets/preset_4.php';
		}

		$textForTask = ($preset??'') ?: ($_POST['text']??'');

		$task2 = new Task2($textForTask);
		$task2->execute();

		$task20 = new Task20($textForTask);
		$task20->execute();

		$data = [
			'TEXT' => $textForTask,
			'TASKS' => [
				'2' => $task2->getResult(),
				'20' => [
					'STYLE' => $task20->getStyleHtml(),
					'RESULT' => $task20->getResult(),
				],
			]
		];
		include 'text_front.php';
	}
}