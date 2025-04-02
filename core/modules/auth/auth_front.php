<?php

?>

<div>
	<h1>Аторизация</h1>

	<?php if ($data['USER_SESSION']['IS_AUTHORIZED']) : ?>
        <div class="alert alert-secondary" role="alert">
            Вы уже авторизованы как <strong><?= $data['USER_SESSION']['DATA']['EMAIL']?></strong> <a href="<?=$data['URI_THIS_PAGE']?>?logout=Y">выйти</a>
        </div>
	<?php else: ?>

		<?php if (count($data['ERROR_MESSAGES'])) : ?>
			<?php foreach ($data['ERROR_MESSAGES'] as $message) : ?>
                <div class="alert alert-danger m-1" role="alert">
					<?=$message?>
                </div>
			<?php endforeach;?>
		<?php endif;?>

        <form method="post" id="form_filter" action="<?=$data['URI_THIS_PAGE']?>">
            <div class="mb-3">
                <label for="email" class="form-label">Email:</label>
                <input type="email" value="<?= $_POST['email'] ?? ''?>" name="email" class="form-control" id="email">
            </div>

            <div class="mb-3">
                <label class="form-label" for="password">Пароль:</label>
                <input type="password" value="<?= $_POST['password'] ?? ''?>" class="form-control mb-1" name="password" id="password">
            </div>

            <input type="hidden" name="action" value="auth">

            <div class="d-flex justify-content-end">
                <input type="submit" class="btn btn-primary m-1" value="войти">
            </div>
        </form>
    <?php endif; ?>
</div>
