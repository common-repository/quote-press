<p style="font-family: sans-serif; font-size: 14px; font-weight: normal; margin: 0; Margin-bottom: 15px;"><?php echo sprintf( __( 'You have received payment for Quote ID: %s via %s.', 'quote-press' ), '{quote_id}', '{quote_payment_option}' ); ?></p>
<table border="0" cellpadding="0" cellspacing="0" class="btn btn-primary" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%; box-sizing: border-box;">
  <tbody>
	<tr>
	  <td align="left" style="font-family: sans-serif; font-size: 14px; vertical-align: top; padding-bottom: 15px;">
		<table border="0" cellpadding="0" cellspacing="0" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: auto;">
		  <tbody>
			<tr>
			  <td style="font-family: sans-serif; font-size: 14px; vertical-align: top; background-color: <?php echo get_option( 'qpr_notification_email_color_primary' ); ?>; border-radius: 5px; text-align: center;"> <a href="<?php echo get_admin_url( '', 'post.php?post={quote_id}&action=edit' ) ?>" target="_blank" style="display: inline-block; color: #ffffff; background-color: <?php echo get_option( 'qpr_notification_email_color_primary' ); ?>; border: solid 1px <?php echo get_option( 'qpr_notification_email_color_primary' ); ?>; border-radius: 5px; box-sizing: border-box; cursor: pointer; text-decoration: none; font-size: 14px; font-weight: bold; margin: 0; padding: 12px 25px; text-transform: capitalize; border-color: <?php echo get_option( 'qpr_notification_email_color_primary' ); ?>;"><?php _e( 'Review Quote' ); ?></a> </td>
			</tr>
		  </tbody>
		</table>
	  </td>
	</tr>
  </tbody>
</table>