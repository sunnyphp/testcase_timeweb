{if isset($caption) && $caption}
	<div class="page-header">
		<h1>{$caption|escape}</h1>
	</div>
{/if}

{if isset($items) && is_array($items)}
	<table class="table table-striped">
	<thead>
		<tr>
			<th>#</th>
			<th>Адрес страницы</th>
			<th>Переменные</th>
			<th>Управление</th>
		</tr>
	</thead>
	<tbody>
	{foreach $items as $item}
		<tr>
			<td>{$item@iteration}</td>
			<td><a href="{$item.link|escape}" target="_blank">{$item.link|escape}</a></td>
			<td>{$item.variables|escape|nl2br}</td>
			<td>
				<button class="btn btn-danger js-delete-page" data-page-index="{$item.key}"><i class="icon-trash icon-white"></i></button>
			</td>
		</tr>
	{foreachelse}
		<tr>
			<td colspan="4">
				<div class="alert alert-info text-center">Нет страниц :(</div>
			</td>
		</tr>
	{/foreach}
	</tbody>
	</table>

	<script>
	$(function(){
		// удаление страницы
		$('.js-delete-page').on('click', function(event){
			event.preventDefault();

			let $this = $(this),
				$row = $this.closest('tr'),
				index = $this.attr('data-page-index') || -1
			;

			$.ajax({
				method: 'POST',
				url: 'admin.php?action=delete',
				dataType: 'json',
				data: {
					index: index
				},
				success: function() {
					$row.hide();
					document.location = 'admin.php?action=listing';
				}
			});
		});
	});
	</script>
{else}
	<div class="alert alert-info">Нет страниц :( <a href="admin.php?action=create">Создать новую!</a></div>
{/if}
