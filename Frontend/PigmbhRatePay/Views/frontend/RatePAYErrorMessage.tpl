<div class='error' style='display: none;'>
    <div id='ratepay_error'></div>
</div>
<script language='javascript'>
    $(document).ready(function() {
    {if $pigmbhErrorMessage}
        $("#ratepay_error").append('{$pigmbhErrorMessage}');
        $("#ratepay_error").parent().show();
    {/if}
    {if $ratepayValidateisAgeValid != 'true'}
        $("#ratepay_error").append('Das angegebene Alter ist unter 18!');
        $("#ratepay_error").parent().show();
    {/if}
    });
</script>