<tr>
	<td align="center" style="padding: 0px 15px;">
	<div class="mktoText" id="footer" mktoname="footer">
		<table role="presentation" class="wMobile" cellpadding="0" cellspacing="0" border="0" style="width: 600px; max-width: 600px;">
		<tr>
			<td class="footer" align="center" valign="top" style="padding-bottom: 50px;">
                <?php if (isset($text) && $text): ?>
                    <p style="margin:0 auto 20px;">
                        <?php echo $text; ?>
                    </p>
                <?php endif; ?>
                <?php if (isset($links) && $links && is_array($links) && count($links)): ?>
                    <p style="mso-line-height-rule:exactly;margin-bottom:20px;">
                        <?php foreach ($links as $link): ?>
                            <a href="<?php echo $link['url']; ?>" class="link" target="_blank" style="color: #0a080b; text-decoration: underline;"><?php echo $link['text']; ?></a>
                            <?php if (next($links)): ?>&nbsp;&nbsp;|&nbsp;&nbsp;<?php endif; ?>
                        <?php endforeach; ?>
                    </p>        
                <?php endif; ?>
			</td>
		</tr>
		</table>
	</div>
	</td>
</tr>
