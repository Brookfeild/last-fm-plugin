jQuery(document).ready(function($){
    $('#lastfm-search-btn').click(function(){
        var username = $('#lastfm-username').val();
        if(!username) { alert('Please enter a username'); return; }

        $('#lastfm-result').html('Loading...');

        $.post(lastfm_ajax_obj.ajax_url, {
            action: 'get_weekly_chart_list',
            username: username
        }, function(response){
            if(response.success){
                $('#lastfm-result').html(response.data);
            } else {
                $('#lastfm-result').html('<span style="color:red;">'+response.data+'</span>');
            }
        });
    });
});
