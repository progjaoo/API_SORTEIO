<?php

function emailSorteado(string $nome, string $nomeSorteio): string
{
    return "
    <div style='font-family: Arial, sans-serif; background:#f5f5f5; padding:20px'>
      <div style='max-width:600px;margin:auto;background:#fff;border-radius:8px'>

        <div style='background:#FF8000;padding:20px;text-align:center'>
          <img 
            src='https://SEU_DOMINIO/logo-radio.png'
            alt='Rádio 89 Maravilha'
            height='60'
          />
        </div>

        <div style='padding:20px'>
          <h2>🎉 Parabéns, {$nome}!</h2>

          <p>
            Você foi o <strong>sorteado</strong> no sorteio:
          </p>

          <p style='font-size:18px'>
            <strong>{$nomeSorteio}</strong>
          </p>

          <p>
            Nossa equipe da <strong>Rádio 89 Maravilha</strong>
            entrará em contato com você em breve.
          </p>

          <p>
            Fique atento ao telefone e ao e-mail 📞📧
          </p>
        </div>

        <div style='background:#eee;padding:10px;text-align:center;font-size:12px'>
          © Rádio 89 Maravilha
        </div>
      </div>
    </div>
    ";
}