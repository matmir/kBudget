$(function() {

    var filterForm = {

        init: function() {

            filterForm.initFilterType();
            filterForm.filterTypeChange();
            filterForm.filterFormSubmit();

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

                        window.location.href = '/analysis/time/month/'+aid+'/'+dateMonth.getMonth()+'/'+dateMonth.getFullYear();
                    } else if (dateType == 'between') {
                        var dateDown = new Date($('#date_from').val());
                        var dateUp = new Date($('#date_to').val());

                        if (dateDown == 'Invalid Date') {
                            dateDown = new Date();
                        }
                        if (dateUp == 'Invalid Date') {
                            dateUp = new Date();
                        }

                        var url = '/analysis/time/between/'+aid+'/';
                        // Date Up
                        url += dateUp.getDate()+'/'+(dateUp.getMonth()+1)+'/'+dateUp.getFullYear()+'/';
                        // Date Down
                        url += dateDown.getDate()+'/'+(dateDown.getMonth()+1)+'/'+dateDown.getFullYear();

                        window.location.href = url;
                    } else {
                        window.location.href = '/analysis/time/all/'+aid;
                    }

                    return false;
                });
            }

        }

    };

    filterForm.init();

});