{if isset($caption) && $caption}
	<div class="page-header">
		<h1>{$caption|escape}</h1>
	</div>
{/if}

<form class="form-horizontal" method="post">
	{foreach $settings as $item}
		{include file="user/setting_item.tpl" item=$item}
	{/foreach}
	<hr>
	<div class="control-group">
		<label class="control-label" for="select_block">Добавить блок</label>
		<div class="controls">
			<select id="select_block" class="js-block-select">
				{foreach $blocks as $block}
					<option value="{$block.value|escape}">{$block.caption|escape}</option>
				{/foreach}
			</select>
			<button class="btn btn-success js-block-add"><i class="icon-plus icon-white"></i> Добавить</button>
		</div>
	</div>

	<div class="form-actions">
		<button type="submit" class="btn btn-primary js-create">Создать</button>
	</div>
</form>

{literal}
<script>
$(function(){
	let $body = $('body'), index = 0;

	// кнопка отправки
	$body.on('click', '.js-create', function(event){
		let $this = $(this),
			$lockButtons = $this.closest('form').find('.js-block-lock')
		;

		if ($lockButtons.length) {
			event.preventDefault();
			alert('Не все блоки зафиксированы');
		}
	});

	// добавление нового блока
	$body.on('click', '.js-block-add', function(event){
		event.preventDefault();

		let $this = $(this),
			$cg = $this.closest('.control-group'),
			name = $('.js-block-select', $cg).find('option:selected').val(),
			$block = $('<div>').text('Загрузка данных…').insertBefore($cg)
		;

		// отправляем XHR-запрос
		create_block($block, {
			name: name,
			index: index++
		});
	});

	// фиксируем выбранные сниппеты в блоке
	$body.on('click', '.js-block-lock', function(event){
		event.preventDefault();

		let $this = $(this),
			$block = $this.closest('[data-block]'),
			index = $block.attr('data-index') || 0,
			name = $block.attr('data-block'),
			$inputs = $('input,select,textarea,button', $block),
			locked_snippets = []
		;

		// блокируем элементы
		$inputs.prop('disabled', true);

		// перебираем варианты
		$.each($('select.js-snippet-variant', $block), function(){
			let $this = $(this),
				value = $('option:selected', $this).val()
			;

			// пропускаем необязательные сниппеты
			if (value !== '0') {
				locked_snippets.push(value);
			}
		});

		// отправляем XHR-запрос
		create_block($block, {
			name: name,
			index: index,
			is_locked: true,
			locked_snippets: locked_snippets
		});
	});

	// удаление блока
	$body.on('click', '.js-block-delete', function(event){
		event.preventDefault();

		let $this = $(this),
			$block = $this.closest('[data-block]')
		;

		$block.slideUp('fast', function(){
			$block.remove();
		});
	});

	/**
	 * Отправка данных для создания блока
	 * @param {object} $block
	 * @param {object} post_data
	 * @return void
	 */
	function create_block($block, post_data)
	{
		if (typeof($block) !== 'object') {
			return;
		}
		if (typeof(post_data) !== 'object') {
			post_data = {};
		}

		$.ajax({
			url: 'admin.php?action=create_block',
			method: 'POST',
			data: post_data,
			dataType: 'html',
			success: function(html){
				$block.replaceWith(html);
			},
			error: function(jqXHR, textStatus, errorThrown){
				$block.html('Error: ' + textStatus + '<br><br>Throw: ' + errorThrown);
				setTimeout(function(){
					$block.fadeOut(2000, function(){
						$block.remove();
					});
				}, 3000);
			}
		});
	}
});
</script>
{/literal}