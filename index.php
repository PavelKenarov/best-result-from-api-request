<html class="js">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Employees list</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0"/>
	<link rel="stylesheet" href="./assets/back-to-top/css/style.css">
    <link rel="stylesheet" href="./assets/bootstrap/bootstrap.min.css">
</head>
<body>
<div class="container">
	<div class="content">
		<div id="employees" style="margin-top:20px"></div>
	</div>
</div>
<a id="backToTop" href="#" class="cd-top text-replace js-cd-top">Top</a>

<footer>
<script src="./assets/jquery/jquery.min.js"></script>
<script src="./assets/bootstrap/bootstrap.min.js"></script>
<script src="./assets/back-to-top/js/util.js"></script>
<script src="./assets/back-to-top/js/main.js"></script>
<script type="text/javascript">
	
	$(document).ready(function () {

		var page = 1;

		$.ajax({
			url: "./load.php?p=" + page,
			type: "GET",
			dataType: "json",
			cache: false,
			success: function (data) {
				
				if (data.length > 0) {
					
					var tpl = '<div class="row col-12" >';
					
					$.each(data, function (k, v) {

						tpl += '<div class="col-6" style="margin:0 0 10px 0; padding:0 0 20px 0; border-bottom:solid 1px #CCC;" >\
						<div class="row"><div class="col-2 hidden-xs"><img src="/assets/users/' + v.avatar + '" alt="" height="64" style="border-radius: 49.9%;" /></div>\
						<div class="col-10"><h4>' + v.name + '</h4><h6>' + v.title + '</h6>\
						<strong>' + v.company + '</strong>';	
						if (v.bio) {
							tpl += '<div class="cv" >' + v.bio + '</div>';
						}
						tpl += '</div></div></div>';

					});
					
					tpl += '</div>';
					tpl += '<div class="col-12 text-center" id="load_more_btn" style="margin:30px 0;">\
					<a class="text-center btn btn-primary" \
					style="background-color:#ed8176;border-color:#ed8176;text-transform:uppercase;color:#FFF;" \
					onclick="load_more(' + (page + 1) + ');">LOAD MORE</a>\
					</div></div>';
					
					$('#employees').html(tpl);

				}
			}
		});

	});

	function load_more(page) {

		$.ajax({
			url: "./load.php?p=" + page,
			type: "GET",
			dataType: "json",
			cache: false,
			success: function (data) {

					$('#load_more_btn').remove();

					var tpl = '<div class="row col-12" >';
					
					$.each(data, function (k, v) {

						tpl += '<div class="col-6" style="margin:0 0 10px 0; padding:0 0 20px 0; border-bottom:solid 1px #CCC;" >\
						<div class="row"><div class="col-2 hidden-xs">\
						<img src="/assets/users/' + v.avatar + '" alt="" height="64" style="border-radius: 49.9%;" /></div>\
						<div class="col-10"><h4>' + v.name + '</h4><h6>' + v.title + '</h6>\
						<strong>' + v.company + '</strong>';	
						if (v.bio) {
							tpl += '<div class="cv" >' + v.bio + '</div>';
						}
						tpl += '</div></div></div>';

					});
					
					tpl += '</div>';
					tpl += '<div class="col-12 text-center" id="load_more_btn" style="margin:30px 0;">\
					<a class="text-center btn btn-primary" \
					style="background-color:#ed8176;border-color:#ed8176;text-transform:uppercase;color:#FFF;" \
					onclick="load_more(' + (page + 1) + ');">LOAD MORE</a>\
					</div></div>';
					
					if (data.length > 0) {
						$('#employees').append(tpl);
					}
			}
		});
	} 
</script>
</footer>
</body>
</html>
