// Gestione cancellazione iscrizione
$(document).on('click', '.cancella-iscrizione', function()
{
    // Numero di gare dell'evento a cui si è iscritto (serve per gestire cancellazione boe)
    let quantita_gare_evento_iscritto = $(this).data('quantita-gare-evento-iscritto');

    let ha_opzioni_acquisto = $(this).data('ha-opzioni-acquisto');

    let modal_cancella_iscrizione_testo = 
        quantita_gare_evento_iscritto == 1 && ha_opzioni_acquisto ?
        'Non è possibile cancellare la gara ' + $(this).data('gara-nome') + ' visto che ci sono delle boe collegate.' :
        'Cancellare iscrizione alla gara ' + $(this).data('gara-nome') + '?';

    // Gestione visibilità bottone per procedere con la cancellazione della gara
    $('#modal-cancella-iscrizione-link').attr('hidden', quantita_gare_evento_iscritto == 1 && ha_opzioni_acquisto ? true : false);

    $('#modal-cancella-iscrizione-titolo').text('Cancella iscrizione');
    $('#modal-cancella-iscrizione-testo').html(modal_cancella_iscrizione_testo);
    $('#modal-cancella-iscrizione-link').attr('href', $('#modal-cancella-iscrizione-link').attr('href') + 'Account/cancellaIscrizione/' + $(this).data('iscrizione-id'));
});

// Gestione cancellazione opzioni acquisto
$(document).on('click', '.cancella-opzione-acquisto', function()
{
    $('#modal-cancella-opzione-acquisto-titolo').text('Cancella opzione acquisto');
    $('#modal-cancella-opzione-acquisto-testo').html('Cancellare ' + $(this).data('opzione-acquisto-nome') + '?');
    $('#modal-cancella-opzione-acquisto-link').attr('href', $('#modal-cancella-iscrizione-link').attr('href') + 'Account/cancellaOpzioneAcquisto/' + $(this).data('opzione-acquisto-id'));
});

// Gestione cancellazione magliette
$(document).on('click', '.cancella-magliette', function()
{
    let acquisti_magliette_id = $(this).data('acquisti-magliette-id');

    $('#modal-cancella-magliette-titolo').text('Cancella magliette');
    $('#modal-cancella-magliette-testo').html('Cancellare le magliette selezionate?');
    $('#modal-cancella-magliette-link').attr('href', $('#modal-cancella-iscrizione-link').attr('href') + 'Account/cancellaMagliette/' + acquisti_magliette_id);
});