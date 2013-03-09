{extends file="frontend/checkout/confirm.tpl"}
{block name='frontend_checkout_confirm_payment' append}
<div class="dispatch-methods">
    <h3 class="underline">RatePAY</h3>
    <p class="none">
        <label for="test">Umsatzsteuer-ID</label>
        <input id="test" type="text">
    </p>
    <p class="none">
        <label for="test2">Telefon-nummer</label>
        <input id="test2" type="text">
    </p>
    <p>
        <input type="checkbox"> AGB
    </p>
</div>
{/block}