jQuery(document).ready(function($){
    $('#lastfm-search-btn').click(function(){
        var username = $('#lastfm-username').val();
        var date = $('#lastfm-date').val(); // Get the date from the input

        if(!username || !date){
            alert('Please enter a username and select a date.');
            return;
        }

        $('#lastfm-result').html('Loading...');

        $.post(lastfm_ajax_obj.ajax_url, {
            action: 'get_weekly_chart_list', // Keep this the same if your PHP handler uses this name
            username: username,
            date: date
        }, function(response){
            if(response.success){
                $('#lastfm-result').html(response.data);
            } else {
                $('#lastfm-result').html('<span style="color:red;">'+response.data+'</span>');
            }
        });
    });
});
