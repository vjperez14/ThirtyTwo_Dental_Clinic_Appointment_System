<?php include('reset_password_logic.php') ?>
<?php  if (count($errors) > 0) : ?>
	<?php foreach ($errors as $error) : ?>
		<span><?php echo $error ?></span>
	<?php endforeach ?>
<?php  endif ?>