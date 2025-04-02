<div>
    <h2>Лабораторная работа №4</h2>

    <div>
        <form method="post" id="form_filter">


            <div class="mb-3">
                <label for="text" class="form-label">Текст:</label>
                <textarea name="text" class="form-control" placeholder="Введите текст" id="text"><?= $data['TEXT']?></textarea>
            </div>

            <div class="d-flex justify-content-end">
                <input type="submit" class="btn btn-primary m-1" value="Отправить">
            </div>
        </form>
    </div>

    <div>
        <h3>Задача №2</h3>
        <div>
            <?= strlen($data['TASKS']['2']) ? $data['TASKS']['2'] : 'Нет картинок на странице'?>
        </div>
    </div>

	<?php if (strlen($data['TASKS']['8'] ?? '')) : ?>
        <div>
            <h3>Задача №8</h3>
            <div>
				<?= $data['TASKS']['8']?>
            </div>
        </div>
	<?php endif;?>
	<?php if (strlen($data['TASKS']['12'] ?? '')) : ?>
        <div>
            <h3>Задача №12</h3>
            <div>
				<?= $data['TASKS']['12']?>
            </div>
        </div>
	<?php endif;?>

    <div>
        <h3>Задача №20</h3>
        <div>
            <h6>Новые классы:</h6>
            <div><pre><?=htmlspecialchars($data['TASKS']['20']['STYLE'] != '<style type="text/css"></style>' ? $data['TASKS']['20']['STYLE'] : 'Повторяющихся style нет в тексте')?></pre></div>
        </div>
        <div>
            <h6>Верстка после выполнения задачи:</h6>
            <div>
                <div><pre><?= htmlspecialchars($data['TASKS']['20']['RESULT'])?></pre></div>
            </div>
        </div>
        <div>
            <h6>Изначальная верска:</h6>
                <pre><?=htmlspecialchars($data['TEXT'])?></pre>
            <div>

            </div>
        </div>


        <div>
            <h6>Вывод верстки в браузер с сгенерированными классами:</h6>
            <div>
                <div><?= $data['TASKS']['20']['RESULT']?></div>
            </div>
        </div>
    </div>

</div>

