{extends file="frontend/checkout/confirm.tpl"}
{block name="frontend_index_content"}
<iframe src="{$gatewayUrl}" style="border:none; height:660px; width: 100%;" name="secupay_iframe"
        id="payment_frame">
    <p>
        Ihr Browser kann leider keine eingebetteten Frames anzeigen:
        Sie k&ouml;nnen die Seite &uuml;ber den folgenden Verweis
        aufrufen: <a href="{$gatewayUrl}">Secupay</a>
    </p>
</iframe>
{/block}