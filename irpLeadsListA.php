<?php
// Zabezpieczenia 
if(!$dostep_koordynator){$alert = 'notAllowed';}

function breadcrumb(){ ?>
<ol class="breadcrumb">
    <li><a href=" /index.php">Panel</a></li>
    <li class="active">IRP 1P</li>
</ol>
<?php }


//Pracownicy-opiekunowie
foreach ($pracownicy as $key => $value) {
    if($value['aktywny']){
        $wypisz_pracownikow .= '<option value="'.$key.'">'.$value['imie'].'</option>';
    }
}

// Podpowiedzi kolejnych tygodni 
$wypisz_tygodnie = "";
$ten_tydzien_poniedzialek = strtotime('last Monday',strtotime('tomorrow'));
$ten_tydzien_niedziela = strtotime('next Sunday',$ten_tydzien_poniedzialek);

$ttp = date('d-m',$ten_tydzien_poniedzialek);
$ttn = date('d-m',$ten_tydzien_niedziela);
$wypisz_tygodnie .= '<option value="'.$ten_tydzien_poniedzialek.'">od '.$ttp.' do '.$ttn.'</option>';

$poprzedni_poniedzialek = $ten_tydzien_poniedzialek;

for ($i = 0; $i < 9; $i++) {

    $kolejny_poniedzialek = strtotime('next Monday',$poprzedni_poniedzialek);
    $kolejna_niedziela    = strtotime('next Sunday',$kolejny_poniedzialek);
    $wypisz_tygodnie .= '<option value="'.$kolejny_poniedzialek.'">od '.date('d-m',$kolejny_poniedzialek).' do '.date('d-m',$kolejna_niedziela).'</option>';    
    $poprzedni_poniedzialek = $kolejny_poniedzialek;
}
    
 
// HTML
breadcrumb();
?>

<div class="containter">
    <div class="page-header">
        <h1>IRP 1P</h1>
    </div>
    <div id="leady-0" class="grupa-leadow">
        <h2>SEGMENT I: Tylko lekcja 3 PR</h2>
        <div class="input-group" style="display: inline-block;">
            <span class="input-group-addon"><b>Przypisz zadanie 'Umówienie rozmowy' do </b></span>
            <span class="input-group-btn">
                <button class="btn btn-primary btn-add-task-to-many" data-ile="5">5 osób</button>
                <button class="btn btn-primary btn-add-task-to-many" data-ile="10">10 osób</button>
                <button class="btn btn-primary btn-add-task-to-checked">Tylko zaznaczone</button>
                <button class="btn btn-warning btn-add-task-to-many" data-ile="0">Odznacz</button>
            </span>
        </div>
        <div>
            <table class="table table-striped tabela-leadow" id="tabela-leadow-0">
                <thead>
                    <tr>
                        <th>Klient</th>
                        <th>Telefon</th>
                        <th>E-mail</th>
                        <th style="width: 60px;">Level</th>
                        <th style="width: 50px;">zaznacz</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
    
    
    <div id="leady-1" class="grupa-leadow">
        <h2>SEGMENT I: Od 4 lekcji PR + Klub</h2>
        <div class="input-group" style="display: inline-block;">
            <span class="input-group-addon"><b>Przypisz zadanie 'Umówienie rozmowy' do </b></span>
            <span class="input-group-btn">
                <button class="btn btn-primary btn-add-task-to-many" data-ile="5">5 osób</button>
                <button class="btn btn-primary btn-add-task-to-many" data-ile="10">10 osób</button>
                <button class="btn btn-primary btn-add-task-to-checked">Tylko zaznaczone</button>
                <button class="btn btn-warning btn-add-task-to-many" data-ile="0">Odznacz</button>
            </span>
        </div>
        <div>
            <table class="table table-striped tabela-leadow" id="tabela-leadow-1">
                <thead>
                    <tr>
                        <th>Klient</th>
                        <th>Telefon</th>
                        <th>E-mail</th>
                        <th style="width: 60px;">Level</th>
                        <th>Nazwa PR</th>
                        <th style="width: 50px;">zaznacz</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<!-- Czarne tlo przetwarzam -->
<div id="czarne-tlo-przetwarzam" class="light-box" style="display: none;"><span class="glyphicon glyphicon-refresh"></span></div>

<!-- Formularz przydzielania zadania-->
<div id="czarne-tlo-przydziel-zadanie-form" class="light-box" style="display: none;">
    <div class="panel panel-info" style="width:300px;">
        <div class="panel-heading">
            <span class="panel-title">Przydziel zadanie</span>
            <div class="pull-right">
                <button class="btn btn-warning btn-xs btn-zamknij-light-box">
                    <span class="glyphicon glyphicon-remove"></span>
                </button>
            </div>
        </div>
        <div class="panel-body max-500-height" >
            <form id="przydziel-zadanie-form">
                <input name="grupa_leadow">
                <div class="form-group">
                    <label for="edytuj-pracownika-input">Opiekun*</label>
                    <select class="form-control" id="edytuj-pracownika-input" name="opiekun">
                        <?=$wypisz_pracownikow?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="edytuj-termin-input">Wybierz termin*</label>
                    <select id="edytuj-termin-input" class="form-control" name="tydzien">
                        <?=$wypisz_tygodnie?>
                    </select>                    
                </div>
                <div><!-- PRZYCISKI ZAPISZ/ANULUJ -->
                    <button type="button" class="btn btn-md btn-warning btn-zamknij-light-box">Anuluj <span class="glyphicon glyphicon-remove"></span></button>
                    <button type="submit" class="btn btn-md btn-success pull-right">Zapisz <span class="glyphicon glyphicon-ok"></span></button>
                </div>
            </form>
        </div>
    </div>
</div>

<script type="text/javascript">
window.onload = function()
{

    // ZAZNACZ WIELE LEADOW I WYŚWIETL FORMULARZ
    $(document).on('click','.btn-add-task-to-many',function(e)
    {
        var target = e.target;
        var amount = target.dataset.ile;
        var parent = $(target).parents('.grupa-leadow');
        var checkboxes = $(parent).find('[data-klient-id]').prop('checked', false);
        for (var i = 0; i < amount; i++) {
            if (checkboxes[i]) {
                $(checkboxes[i]).prop('checked', true);
            }
        }
        $(parent).find('.btn-add-task-to-checked')
    });
    

    //ładne formatowanie tabeli
    $('.tabela-leadow').DataTable({
        "retrieve": true,
        "order": [],
        "iDisplayLength": 25,
        "language": {"url": "https://cdn.datatables.net/plug-ins/1.10.7/i18n/Polish.json"},
        fnDrawCallback: function(){$("[data-toggle='tooltip']",this.fnGetNodes()).tooltip({"delay": 0,"track": true,"fade": 250});}
    });
    
    // WYŚWIETL FORMULARZ TYLKO DLA ZAZNACZONYCH
    $(document).on('click','.btn-add-task-to-checked',function(e)
    {
        e.preventDefault();
        var grupa_leadow = $(this).parents('.grupa-leadow');
        var zaznaczone = $('input[data-klient-id]:checked',grupa_leadow);
        var len = zaznaczone.length;
        if(len){
            $('#czarne-tlo-przydziel-zadanie-form').show();
            $('#czarne-tlo-przydziel-zadanie-form input[name="grupa_leadow"]').val($(grupa_leadow).attr('id'));
        }
        else{
            bootbox.alert('Zaznacz przynajmniej jednego leada.');
            return false;
        }
    });


    $(document).on('submit','#przydziel-zadanie-form',function(e)
    {
        e.preventDefault();
       
        var czy_pusta = $('#edytuj-termin-input').val().trim()==='' || $('#edytuj-pracownika-input').val().trim()==='';
        if(czy_pusta){
            bootbox.alert("Uzupełnij wszystkie wymagane pola.");
            return false;
        }
        
        var tydzien= $('#edytuj-termin-input').val();
        var pracownik= $('#edytuj-pracownika-input').val();
        var grupa_leadow = $('#'+$('#czarne-tlo-przydziel-zadanie-form input[name="grupa_leadow"]').val());
        var zaznaczone  = $('input[data-klient-id]:checked',grupa_leadow);
        var len = zaznaczone.length;
        var ids = [];
        for ( var i = 0; i < len; i++ ) {
          ids.push( $(zaznaczone[i]).attr('data-klient-id'));
        }
        if(len){
            $.ajax({
                url: "zadania/zadania_PrzydzielTygodnioweAjax.php",
                method: "POST",
                data: {
                    user:'<?=$user_mod?>',
                    tydzien:tydzien,
                    pracownik:pracownik,
                    ids:ids,
                    typ_zadania:106
                },
                dataType: "json",
                beforeSend: function(){pokazPrzetwarzam();}
            })
            .success(function(data){
                if(data.status === 'success'){
                    $('input[data-klient-id]:checked',grupa_leadow).prop('checked',false);
                    bootbox.alert("Przypisano nowe zadania sprzedawcom.");
                    for (var i = 0; i < ids.length; i++) {
                        $('#klient-'+ids[i]+'-row').addClass('danger').hide('slow');
                    }
                }
                else if(data.status === 'error'){
                    bootbox.alert('Błąd: '+data.message);
                }
                else{bootbox.alert("Nie udało się wykonać żądania. Błąd: "+data.message);}
            })
            .fail(function(){bootbox.alert("Nie udało się wysłać żądania.");})
            .complete(function(){$('#czarne-tlo-przydziel-zadanie-form,#czarne-tlo-przetwarzam').hide();});
        }
        else{
            bootbox.alert('Zaznacz przynajmniej jednego leada.');
            return false;
        }
    });
    

    $(document).ready(pokaz_leady());
    
};



function  pokaz_leady(){
    $.ajax({
        url: "irpLeadsListAAjax.php",
        method: "POST",
        data: {user:'<?=$user_mod?>'},
        dataType: "json",
        beforeSend: function(){pokazPrzetwarzam();}
    })
    .success(function(data){
        if(data.status === 'success')
        {
            for (var i = 0, max = 2; i < max; i++)
            {
                table = $('#tabela-leadow-'+i).DataTable();
                table.clear();
                $.each(data.dane[i], function(i,v)
                {
                    var level = v.level === 'klient' ? '<span class="label label-primary">KLIENT</span>' : '<span class="label label-success">UCZESTNIK</span>';   
                    var rowArray = [
                        '<a href="/klientKarta&id='+v.klient_id+'" target="_blank">'+v.klient+'&nbsp;<small><span class="glyphicon glyphicon-new-window"></span></small></a>', 
                        v.telefon,
                        v.email,
                        level,
                        '<input data-klient-id="'+v.klient_id+'" type="checkbox">'
                    ];             
                    if (v.produkt_name) {
                        rowArray.splice(4, 0, v.produkt_name);
                    }
                    var rowNode = table.fnAddData(rowArray);
                    $(rowNode).attr('id','klient-'+v.klient_id+'-row');
                    $('td:eq(4),td:eq(5)',rowNode).addClass( 'text-center');
                });
                table.draw();
            }
};

</script>
