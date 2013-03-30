function Zakryj() {
    if ($('#filter_type').val()=='between') {
        $('.month_filter').hide();
        $('.between_filter').show();
    } else if ($('#filter_type').val()=='all') {
        $('.month_filter').hide();
        $('.between_filter').hide();
    } else {
        $('.month_filter').show();
        $('.between_filter').hide();
    }
};
 
// Załadowanie dokumentu
$(document).ready(function(){
    
    // Domyślne formatki
    Zakryj();
    
    // Zmieniono selekta od typu filtracji
    $('#filter_type').change(function(){
        if ($('option:selected', this).val() == 'month') {
            $('.month_filter').show();
            $('.between_filter').hide();
        }
        if ($('option:selected', this).val() == 'between') {
            $('.month_filter').hide();
            $('.between_filter').show();
        }
        if ($('option:selected', this).val() == 'all') {
            $('.month_filter').hide();
            $('.between_filter').hide();
        }
    });
 
});
