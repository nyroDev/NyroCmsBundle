<center>
	<table border="0" cellpadding="0" cellspacing="0" height="100%" width="100%" id="backgroundTable">
		<tr>
			<td align="center" valign="top">
				<br />
				<table border="0" cellpadding="0" cellspacing="0" width="600" id="templateContainer">
					<tr>
						<td align="center" valign="top">
							<table border="0" cellpadding="8" cellspacing="0" width="600">
								<tr>
									<td valign="top" align="center">
										<br /><br />
										<a href="<?php echo $view['nyrodev']->generateUrl('_homepage', [], true); ?>" target="_blank"><img src="<?php echo $view['nyrodev']->getFullUrl($view['assets']->getUrl('images/email.png')); ?>" alt="email" width="125" height="125" /></a>
										<br /><br />
									</td>
								</tr>
								<tr>
									<td valign="top" align="left">
										<?php echo $content; ?>
										<br /><br /><br />
									</td>
								</tr>
							</table>
						</td>
					</tr>
					<tr>
						<td align="center" valign="top">
							<table border="0" cellpadding="8" cellspacing="0" width="600" style="background-color:#23305c;">
								<tr>
									<td valign="top" align="center">
										<a href="<?php echo $view['nyrodev']->generateUrl('_homepage', [], true); ?>" style="color:#fff;font-weight:bold;text-decoration:none;font-size:16px;" target="_blank">website</a>
									</td>
								</tr>
							</table>
						</td>
					</tr>
				</table>
				<br />
			</td>
		</tr>
	</table>
</center>