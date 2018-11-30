{extends file="frontend/checkout/confirm.tpl"}


{* Main content *}
{block name='frontend_index_content'}
<div id="center" class="error">

    <h2>{$errorMsg}</h2>        
</div>
<div class="actions">
	<br />
	<br />
	<br />
	<br />
	<br />
	<br />
        <a class="button-left large left" href="{url controller=checkout action=cart}" title="Zur&uuml;ck zum Warenkorb">
		Zur&uuml;ck zum Warenkorb
	</a>
</div>
{/block}