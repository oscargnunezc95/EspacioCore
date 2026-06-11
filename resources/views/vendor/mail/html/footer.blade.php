<tr>
    <td>
        <table class="footer" align="center" width="570" cellpadding="0" cellspacing="0" role="presentation">
            <tr>
                <td class="content-cell" align="center">
                    {{ Illuminate\Mail\Markdown::parse($slot) }}
                    
                    {{-- Tu nuevo bloque de soporte global --}}
                    <p style="margin-top: 15px; font-size: 13px; color: #718096;">
                        ¿Necesitas ayuda o tienes alguna duda? <br>
                        Escríbenos a <a href="mailto:{{ config('mail.support_email') }}" style="color: #3182ce; text-decoration: none;">{{ config('mail.support_email') }}</a>
                    </p>
                </td>
            </tr>
        </table>
    </td>
</tr>