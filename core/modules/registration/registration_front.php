<?php
$genders = $data['GENDERS'];
?>

<div>
	<h1>Регистрация</h1>

    <?php if ($data['USER_SESSION']['IS_AUTHORIZED']) : ?>
        <div class="alert alert-secondary" role="alert">
            Вы уже авторизованы как <strong><?= $data['USER_SESSION']['DATA']['EMAIL']?></strong> <a href="<?= $data['URI_THIS_PAGE']?>?logout=Y">выйти</a>
        </div>
    <?php else: ?>

		<?php if (count($data['ERROR_MESSAGES'])) : ?>
			<?php foreach ($data['ERROR_MESSAGES'] as $message) : ?>
                <div class="alert alert-danger m-1" role="alert">
					<?=$message?>
                </div>
			<?php endforeach;?>
		<?php endif;?>

        <form method="post" action="<?= $data['URI_THIS_PAGE']?>">
            <div class="mb-3">
                <label for="fio" class="form-label">ФИО:</label>
                <input type="text" value="<?= $_POST['fio'] ?? ''?>" name="fio" class="form-control" id="fio">
            </div>

            <div class="mb-3">
                <label class="form-label" for="date_of_birth">Дата рождения:</label>
                <input type="date" value="<?= $_POST['date_of_birth'] ?? ''?>" class="form-control mb-1" name="date_of_birth" id="date_of_birth">
            </div>

            <div class="mb-3">
                <label for="address" class="form-label">Адрес:</label>
                <input type="text" value="<?= $_POST['address'] ?? ''?>" name="address" class="form-control" id="address">
            </div>

            <div class="mb-3">
                <label for="gender_id" class="form-label">Пол:</label>
                <select id="gender_id" name="gender_id" class="form-control">
                    <option value="" <?= (isset($_POST['gender_id']) && intval($_POST['gender_id'])) ? '' : 'selected'?> >Выберите пол</option>
					<?php foreach ($genders as $gender) : ?>
                        <option <?= (isset($_POST['gender_id']) && $_POST['gender_id'] == $gender['ID']) ? 'selected' : ''?> value="<?=$gender['ID']?>"><?=$gender['NAME']?></option>
					<?php endforeach;?>
                </select>
            </div>

            <div class="mb-3">
                <label for="interests" class="form-label">Интересы:</label>
                <textarea name="interests" class="form-control" id="interests"><?= $_POST['interests'] ?? ''?></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label" for="link_vk">Ссылка на вк:</label>
                <input type="text" value="<?= $_POST['link_vk'] ?? ''?>" class="form-control mb-1" name="link_vk" id="link_vk">
            </div>

            <div class="mb-3">
                <label class="form-label" for="blood_type">Группа крови:</label>
                <input type="text" value="<?= $_POST['blood_type'] ?? ''?>" class="form-control mb-1" name="blood_type" id="blood_type">
            </div>

            <div class="mb-3">
                <label class="form-label" for="rh_factor">Резус фактор:</label>
                <input type="text" value="<?= $_POST['rh_factor'] ?? ''?>" class="form-control mb-1" name="rh_factor" id="rh_factor">
            </div>

            <div class="mb-3">
                <label class="form-label" for="email">EMAIL:</label>
                <input type="email" value="<?= $_POST['email'] ?? ''?>" class="form-control mb-1" name="email" id="email">
            </div>

            <div class="mb-3">
                <label class="form-label">Пароль:</label>
                <input type="password" value="<?= $_POST['password'] ?? ''?>" class="form-control mb-1" placeholder="введите пароль" name="password" id="password">
                <input type="password" value="<?= $_POST['password_repeat'] ?? ''?>" class="form-control" placeholder="повторите пароль" name="password_repeat" id="password_repeat">
            </div>

            <input type="hidden" name="action" value="registration">

            <div class="d-flex justify-content-end">
                <input type="submit" class="btn btn-primary m-1" value="Зарегистрироваться">
            </div>
        </form>
    <?php endif; ?>

</div>