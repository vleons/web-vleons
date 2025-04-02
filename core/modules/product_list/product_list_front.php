<?php
$sales = $data['SALES'];
$products = $data['PRODUCTS'];
$nameFields = [
    'ID' => 'ID',
    'IMG' => 'Изображение',
    'NAME' => 'Название',
    'DESCRIPTION' => 'Описание',
    'SALE_NAME' => 'Скидка',
    'PRICE' => 'Цена',
];
?>
<div class="m-5">
    <h2>Фильтрация результата поиска</h2>
    <form method="get" id="form_filter">
        <div class="mb-3">
            <label for="product_id" class="form-label">ID:</label>
            <input type="number" value="<?= $_GET['product_id'] ?? ''?>" name="product_id" class="form-control" id="product_id">
        </div>

        <div class="mb-3">
            <label class="form-label">Цена</label>
            <input type="number" value="<?= $_GET['price_from'] ?? ''?>" class="form-control mb-1" name="price_from" placeholder="Цена от" id="price_from">
            <input type="number" value="<?= $_GET['price_to'] ?? ''?>" class="form-control" name="price_to" placeholder="Цена до" id="price_to">
        </div>

        <div class="mb-3">
            <label for="name" class="form-label">Название:</label>
            <input type="text" value="<?= $_GET['name'] ?? ''?>" name="name" class="form-control" id="name">
        </div>

        <div class="mb-3">
            <label for="description" class="form-label">Описание:</label>
            <textarea name="description" class="form-control" id="description"><?= $_GET['description'] ?? ''?></textarea>
        </div>

        <div class="mb-3">

            <label for="saleId" class="form-label">Скидка:</label>
            <select id="saleId" name="saleId" class="form-control">
                <option value="" <?= (isset($_GET['saleId']) && intval($_GET['saleId'])) ? '' : 'selected'?> >Выберите скидку</option>
                <?php foreach ($sales as $sale) : ?>
                    <option <?= (isset($_GET['saleId']) && $_GET['saleId'] == $sale['ID']) ? 'selected' : ''?> value="<?=$sale['ID']?>"><?=$sale['NAME']?></option>
                <?php endforeach;?>
            </select>
        </div>

        <div class="d-flex justify-content-end">
            <input type="submit" class="btn btn-primary m-1" value="Отправить">
            <input type="submit" class="btn btn-danger m-1" name="clearFilter" value="Сбросить">
        </div>


    </form>
</div>

<?php if (count($products)) : ?>
	<table class="table">
		<thead>
			<tr>
				<?php  foreach ($products[0] as $codeField => $valueField) : ?>
                    <?php if (isset($nameFields[$codeField])) : ?>
                        <th scope="col"><?= $nameFields[$codeField] ?></th>
                    <?php endif; ?>
				<?php endforeach; ?>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($products as $product) : ?>
				<tr>
					<?php foreach ($product as $codeField => $valueField) : ?>
                        <?php if (isset($nameFields[$codeField])) : ?>
							<?php if ($codeField == 'IMG') : ?>
                                <td>
                                    <img style="width: 200px; height: auto" src="<?= $valueField ?>">
                                </td>
                            <?php elseif ($codeField == 'PRICE') :?>
                                <td><?= htmlspecialchars($valueField) ?>р</td>
							<?php else : ?>
                                <td><?= htmlspecialchars($valueField) ?></td>
							<?php endif; ?>
                        <?php endif; ?>
					<?php endforeach; ?>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
<?php endif; ?>
