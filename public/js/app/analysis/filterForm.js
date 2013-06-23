$(function() {

    var filterForm = {

        init: function() {

            filterForm.initFilterType();
            filterForm.filterTypeChange();
            filterForm.filterFormSubmit();
            filterForm.filterClick();

        },
        // Show/hide filter form
        filterClick: function() {
            $('a#filterForm').click(function() {
                if ($(this).text() == 'Filtracja +') {
                    $(this).text('Filtracja -');
                } else {
                    $(this).text('Filtracja +');
                }
                $('#transaction-time-select').toggle();
                return false;
            });
        },
        // Default options for date filters
        initFilterType: function() {

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

        },
        // Change filter type
        filterTypeChange: function() {

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

        },
        // Filter form submit
        filterFormSubmit: function() {

            if ($('#transaction-time-select').length > 0) {
                $('#transaction-time-select').submit(function() {

                    // Action name
                    var action = $('#transaction-time-select').attr('action').split('/')[2];
                    // Bank account identifier
                    var aid = $('#aid').val();
                    // Get Date type
                    var dateType = $('#filter_type').val();

                    if (dateType == 'month') {
                        var month = $('#month').val();
                        var year = $('#year').val();

                        var dateMonth = new Date();
                        dateMonth.setMonth(month);
                        dateMonth.setFullYear(year);

                        if (dateMonth == 'Invalid Date') {
                            dateMonth = new Date();
                        }

                        window.location.href = '/analysis/'+action+'/month/'+aid+'/'+dateMonth.getMonth()+'/'+dateMonth.getFullYear();
                    } else if (dateType == 'between') {
                        var dateDown = new Date($('#date_from').val());
                        var dateUp = new Date($('#date_to').val());

                        if (dateDown == 'Invalid Date') {
                            dateDown = new Date();
                        }
                        if (dateUp == 'Invalid Date') {
                            dateUp = new Date();
                        }

                        var url = '/analysis/'+action+'/between/'+aid+'/';
                        // Date Up
                        url += dateUp.getDate()+'/'+(dateUp.getMonth()+1)+'/'+dateUp.getFullYear()+'/';
                        // Date Down
                        url += dateDown.getDate()+'/'+(dateDown.getMonth()+1)+'/'+dateDown.getFullYear();

                        window.location.href = url;
                    } else {
                        window.location.href = '/analysis/'+action+'/all/'+aid;
                    }

                    return false;
                });
            }

        }

    };

    filterForm.init();

});
