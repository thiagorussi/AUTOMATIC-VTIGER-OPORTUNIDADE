const express = require('express');
const bodyParser = require('body-parser');

const app = express();
const PORT = 3000;

// Middleware para processar JSON
app.use(bodyParser.json());

// Rota principal do webhook
app.post('/webhook', (req, res) => {
    console.log('Recebido um webhook:', req.body);
    // Resposta de sucesso
    res.status(200).send({ message: 'Webhook recebido com sucesso!' });
});

// Iniciar o servidor
app.listen(PORT, () => {
    console.log(`Servidor rodando em http://localhost:${PORT}`);
});


