<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?>
<?php $i=1 ?>
<?php $voucher = $voucher->row(); ?>

<html>
<head>
</head>
<body>
<table width=220px>
	<tr><td align='center'>
	<?=$this->config->item('company_name')?><br />
	<?=$this->config->item('company_address_line1')?><br />
	<?=$this->config->item('company_address_line2')?><br />
	<?=$this->config->item('company_address_line3')?>
	<hr />
	</td></tr>
	<tr>
		<td>
			<table width=100%>
			<tr><td width=60%><?=$this->lang->line('username')?></td><td>: <?=$voucher->username?></td></tr>
			<tr><td><?=$this->lang->line('password')?></td><td>: <?=$voucher->password?></td></tr>	
			<tr><td><?=$this->lang->line('billing_plan')?></td><td>: <?=$voucher->billingplan?></td></tr>
			<tr><td><?=$this->lang->line('valid')?></td><td>: <?=$voucher->amount?> <?=($voucher->type == 'time') ? 'Minutes' : 'MB';?></td></tr>
			<tr><td><?=$this->lang->line('price')?></td><td>: <?=$this->config->item('currency_symbol')?><?=$voucher->price?></td></tr>
			</table>
		</td>
	</tr>
	<tr>
		<td align='center'><hr /><?=unix_to_human(time())?></td>
	</tr>
	<tr>
		<td align='center'><?=$this->lang->line('thanks')?></td>
	</tr>
</table>
</body>
</html>
		