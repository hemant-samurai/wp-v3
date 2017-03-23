<?php
/*
Template Name: Custom PHP Page
*/
?>
<!DOCTYPE html>
	<html lang="en">
	<head>
		<meta charset="UTF-8">
		<title>Just a Simple PHP Page</title>
		<style>
			.container {
				max-width: 960px;
				margin: 0 auto;
				background: rgb(245,244,245);
				padding: 15px;
				margin-top: 80px;
				line-height: 1.7;
			}
			a {
				text-decoration: none;
			}
		</style>
	</head>
	<body>
		<div class="container">
			<h1>Demo cutom PHP page | Can be any PHP Code</h1>
			<hr />
			<p>
				Lorem ipsum dolor sit amet, consectetur adipisicing elit. Soluta vel voluptates a perferendis quis sapiente in magni, odio eveniet impedit natus id voluptate quod exercitationem cumque ipsum numquam eligendi quidem.
			</p>
			<p>
				<span>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Quisquam assumenda officia vel dolorem quaerat nihil sequi eius doloremque sunt iure illo molestias in, nulla autem distinctio veritatis ipsam, eos saepe.</span>
				<span>Suscipit laboriosam debitis, iure cum? Necessitatibus accusamus culpa magnam, tempore temporibus. Repellat et voluptatum quidem laboriosam fuga atque, qui, hic aspernatur maxime iusto error nesciunt, explicabo culpa dolore, tempore voluptates?</span>
				<span>Et provident, alias enim quas recusandae quis numquam architecto minima tempora qui ducimus, dolor quidem facilis repellat ipsum doloribus ab mollitia odio culpa. Velit similique, placeat officia nisi pariatur possimus?</span>
				<span>Similique, aliquid, libero quae nobis tempora enim perferendis fuga excepturi corrupti dicta blanditiis expedita eaque, ipsum architecto. Suscipit soluta necessitatibus ea. Modi doloribus ipsum temporibus dolores aliquid, facere porro vero.</span>
				<span>Similique sapiente placeat fuga dignissimos ad quae neque non quidem dicta deserunt iste facere repellendus sit eius ducimus totam aspernatur nihil, error dolorem, porro, atque earum. Libero quis, id veniam.</span>
			</p>
			<button><a href="<?php echo get_home_url(); ?>">Home</a></button>
		</div>
	</body>
	</html>	