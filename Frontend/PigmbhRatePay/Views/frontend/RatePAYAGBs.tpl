<div>
    <p class="none">
        <input type="checkbox" id='ratepay_agb'>
        {s name='ratepay_agb_first'}Ich habe die Allgemeinen Geschäftsbedingungen zur Kenntnis genommen und erkläre mich mit
        deren Geltung einverstanden. Außerdem erkläre ich hiermit meine Einwilligung zur Verwendung
        meiner Daten gemäß der {/s}

        <a href='http://customers.ratepay.com/dse.html' >RatePAY-Datenschutzerklärung</a>

        {s name='ratepay_agb_last'}und bin insbesondere damit einverstanden, zum Zwecke der Durchführung
        des Vertrages über die von mir angegebene E-Mail-Adresse kontaktiert zu werden.{/s}
    </p>
</div>
<script language='javascript'>
    $(document).ready(function() {
    $('#basketButton').attr('disabled','disabled');
    $("#basketButton").css({ opacity: 0.5 });
$("#basketButton").attr('title', '{s name="ratepay_agb_mouseover"}Um RatePAY nutzen zu können müssen sie den AGBs von RatePAY zustimmen{/s}');
$('#ratepay_agb').click(function(){
if($(this).prop('checked')){
$("#basketButton").removeAttr('disabled');
$("#basketButton").removeAttr('title');
$("#basketButton").css({ opacity: 1.0 });
}else{
$("#basketButton").attr('disabled','disabled');
$("#basketButton").attr('title', '{s name="ratepay_agb_mouseover"}Um RatePAY nutzen zu können müssen sie den AGBs von RatePAY zustimmen{/s}');
$("#basketButton").css({ opacity: 0.5 });
}
});
});
</script>