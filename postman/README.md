# Postman - OCR System Simples

## Arquivos

- `Laravel-OCR-System-Simples.postman_collection.json`

## Como importar

1. Abra o Postman.
2. Clique em `Import`.
3. Importe apenas o arquivo de collection.
4. Ajuste as variaveis da collection se necessario (`base_url`, `document_id`, `query`).

## Ordem recomendada de testes

1. `1) OCR - Upload Arquivo`
2. `3) Historico - Detalhe por ID` (repita ate `processing_status = completed`)
3. `2) Historico - Listar`
4. `4) Health Check`
5. `5) API Health Check`

## Observacoes

- O upload ja vem apontando para um arquivo de teste local:
  - `C:/laragon/www/laravel-ocr-system-simples/test-files/03-nota-fiscal.png`
- Se quiser, troque esse arquivo no body `form-data`.
- A request de upload salva o ID automaticamente em `{{document_id}}`.
