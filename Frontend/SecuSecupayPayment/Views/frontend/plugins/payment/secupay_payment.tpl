<!-- paymentLogo -->
<div class="secupay_delivery_warning">
	{if $secupay_show_alt_delivery_warning == 1}
		{if $secupay_delivery_address_differs == 1}
			<p>{s name='SecupayDebitAlternateDeliveryText'}
					<b>Achtung: Der Versand erfolgt auschlie&szlig;lich an die angegebene Rechnungsadresse.</b>
				{/s}
			</p><br/>
		{/if}
	{/if}
</div>