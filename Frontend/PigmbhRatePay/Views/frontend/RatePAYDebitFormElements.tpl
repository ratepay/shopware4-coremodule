{if $ratepayValidateisDebitSet == 'false'}
    <input id='ratepay_debit_updatedebitdata' type='hidden' value='true'>
    <p class='none'>
        <label for='ratepay_debit_accountnumber'>Kontonummer:</label>
        <input id='ratepay_debit_accountnumber' type='text' class='text'>
    </p>
    <p class='none'>
        <label for='ratepay_debit_bankcode'>Bankleitzahl:</label>
        <input id='ratepay_debit_bankcode' type='text' class='text'>
    </p>
    <p class='none'>
        <label for='ratepay_debit_bankname'>Bankname:</label>
        <input id='ratepay_debit_bankname' type='text' class='text'>
    </p>
    <p class='none'>
        <label for='ratepay_debit_accountholder'>Konto-Inhaber:</label>
        <input id='ratepay_debit_accountholder' type='text' class='text'>
    </p>
{/if}