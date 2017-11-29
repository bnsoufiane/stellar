<div class="messages">
</div>

<script>
    $(function () {

        function outreach() {
            $.ajax({
                url: '/outreach',
                type: 'GET',
                dataType: 'json'
            }).success(function (response) {
                $('.messages').append('<p>' + response.response + '</p>');
                //$("html, body").animate({ scrollTop: $(document).height() }, 500);
            }).error(function (err) {
                console.info(err);
            });
        }

        outreach();
        setInterval(function () {
            outreach();
        }, 5000);
    })
</script>