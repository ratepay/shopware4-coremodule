{if $ratepayValidateisDebitSet == 'false'}
    <input id='ratepay_debit_updatedebitdata' type='hidden' value='true'>
    <p class='none'>
        <label for='ratepay_debit_accountnumber'>{s namespace=RatePAY name=accountNumber}Kontonummer{/s}:</label>
        <input id='ratepay_debit_accountnumber' type='text' class='text'>
    </p>
    <p class='none'>
        <label for='ratepay_debit_bankcode'>{s namespace=RatePAY name=bankCode}Bankleitzahl{/s}:</label>
        <input id='ratepay_debit_bankcode' type='text' class='text'>
    </p>
    <p class='none'>
        <label for='ratepay_debit_bankname'>{s namespace=RatePAY name=bankName}Bankname{/s}:</label>
        <input id='ratepay_debit_bankname' type='text' class='text'>
    </p>
    <p class='none'>
        <label for='ratepay_debit_accountholder'>{s namespace=RatePAY name=accountHolder}Konto-Inhaber{/s}:</label>
        <input id='ratepay_debit_accountholder' type='text' class='text'>
    </p>
{/if}