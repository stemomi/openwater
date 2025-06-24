$(document).ready(function() 
{
    var data_nascita = $('#data-nascita').val();
    var DataNascitaArray = data_nascita.split("/");
    var anno_data_nascita = DataNascitaArray[2];
    var anno_data_nascita_massima = $('#anno-data-nascita-massima').val();

    if (anno_data_nascita == anno_data_nascita_massima)
        $('#dichiarazione-iscritto-ha-10-anni').attr('hidden', false);

    // Conferma Button
    $(document).on('click','#ConfermaButton', function()
    {
        var Errori = 0;

        var CodiceFiscale = $('#CodiceFiscale').val();
        var PaeseEstero = $('#PaeseEstero').val();
        var data_nascita = $('#data-nascita').val();

        // Validazione codice fiscale o paese straniero
        if (CodiceFiscale == '' && PaeseEstero == '')
        {
            Errori = 1;
            $('#ErroreForm').html('<span class="text-danger">Compilare codice fiscale, o paese estero se straniero!</span>');
        }

        // Validazione data di nascita (minimo 10 anni)
        if (data_nascita != '')
        {
            var DataNascitaArray = data_nascita.split("/");
            var anno_data_nascita = DataNascitaArray[2];

            if (anno_data_nascita > anno_data_nascita_massima)
            {
                Errori = 1;
                $('#ErroreForm').html('<span class="text-danger">Per poterti iscrivere devi avere almeno 10 anni!</span>');
            }
            
            if ($('#dichiarazione-iscritto-ha-10-anni-checkbox').is(':checked') == false && $('#dichiarazione-iscritto-ha-10-anni').attr('hidden') == undefined)
            {
                Errori = 1;
                $('#ErroreForm').html('<span class="text-danger">Devi accettare la dichiarazione presente sotto il campo "Data di nascita"!</span>');
            }
        }

        // Submit form
        if (Errori == 0)
        {
            $('#ErroreForm').html('');
            $('#ConfermaSubmit').click();
        }
    });

    $(document).on('change', '#data-nascita', function()
    {
        console.log('test');
        var DataNascitaArray = $(this).val().split('/');
        var anno_data_nascita = DataNascitaArray[2];

        console.log(anno_data_nascita, anno_data_nascita_massima );

        // Gestione visibilità dichiarazione iscritto ha 10 anni
        $('#dichiarazione-iscritto-ha-10-anni').attr('hidden', !(anno_data_nascita == anno_data_nascita_massima));
    });
});
