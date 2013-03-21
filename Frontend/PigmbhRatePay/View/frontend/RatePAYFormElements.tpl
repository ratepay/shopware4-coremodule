<div class='error' style='display: none;'>
    <div id='ratepay_error'></div>
</div>
{if $ratepayValidateIsB2B == 'true'}
    {if $ratepayValidateUST != 'true'}
        <p class='none'>
            <label for='ratepay_ustid'>Umsatzsteuer:</label>
            <input id='ratepay_ustid' class='text' type="text">
        </p>
    {/if}
    {if $ratepayValidateCompanyName != 'true'}
        <p class='none'>
            <label for='ratepay_phone'>Firmenname:</label>
            <input id='ratepay_phone' class='text' type="text">
        </p>
    {/if}
{/if}
{if $ratepayValidateTelephoneNumber != 'true'}
    <p class='none'>
        <label for='ratepay_phone'>Telefonnummer:</label>
        <input id='ratepay_phone' class='text' type="text">
    </p>
{/if}
{if $ratepayValidateIsBirthdayValid != 'true' || $ratepayValidateisAgeValid != 'true'}
    <p class='none'>
        <label for='ratepay_birthday'>Geburtsdatum:</label>
        <input id='ratepay_birthday' class='text' type="text" value="{$sUserData.billingaddress.birthday}" readonly>
    </p>
{/if}

<script language='javascript'>
    $(document).ready(function() {
    {if $ratepayValidateIsBirthdayValid != 'true' || $ratepayValidateisAgeValid != 'true'}
        $("#ratepay_birthday").datepicker(
            {
                dateFormat: "yy-mm-dd",
                changeYear: true,
                changeMonth: true,
                yearRange: "c-100:c"
            }
        );
    {/if}
    {if $ratepayValidateisAgeValid != 'true'}
    $("#ratepay_error").append('Das angegebene Alter ist unter 18!');
    $("#ratepay_error").parent().show();
    {/if}


    $("#basketButton").click(function(){
        var requestParams = 'userid=' + "{$sUserData.billingaddress.userID}";
        var userUpdate = false;
        $('input[id^="ratepay_"]').each(function(){
            userUpdate = true;
            requestParams += '&'+$(this).attr('id') +'='+ $(this).val();
        });
        if(userUpdate){
            $.ajax({
                type: "POST",
                async: false,
                url: "{url controller='PigmbhRatepay' action='saveUserData'}",
                data: requestParams
            }).done(function( msg ) {
                if(msg == 'OK'){
                    console.log('UserDaten erfolgreich aktualisiert.');
                }else{
                    console.log('Fehler beim Aktualisieren der UserDaten. Return: ' + msg);
                }
            });
        }
    });
});
</script>