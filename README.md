# CEP-Cash - Symfony API Project

This project provides an API endpoint that fetches address information from the [ViaCEP API](https://viacep.com.br/), caches it in Redis, and returns the cached response in JSON format. The project is built using Symfony, and the development environment is configured to run in Docker containers.

## Prerequisites

- **Docker**: Make sure Docker is installed on your machine.
- **Docker Compose**: Ensure Docker Compose is available to manage containerized services.

## Getting Started

### 1. Clone the Repository

```bash
git clone https://github.com/your-username/your-repo-name.git
cd your-repo-name
```
### 2. Build and Run the Containers
Run the following command to build and start the containers:
```bash
docker-compose up --build
```
This will set up the following services:

- **Symfony Application**: Accessible at `http://localhost:8080`
- **Redis**: Used for caching API responses

> **Note**: Port mappings may vary depending on your Docker configuration. Adjust the ports in `docker-compose.yml` if needed.

### 3. Install Dependencies

After the containers are up, install the Symfony project dependencies.

```bash
sudo docker-compose exec app bash -c "composer install"
```
This command runs ``composer install`` inside the Symfony container.

Access the container and manually install the Redis extension and configuration in ``php.ini``:
```bash
docker-compose exec app bash
pecl install redis
```
```ini
extension=redis.so
```

### 5. Access the Application
Once the setup is complete, you can access the application by visiting:

```
http://localhost:8080/cep/{cep}
```

Replace {cep} with a valid Brazilian postal code (CEP) to query address information.

## Usage

- **API Endpoint**: `/cep/{cep}` (GET)  
  - This endpoint fetches address data from the ViaCEP API for the given `{cep}`.
  - If the address is already cached, it returns the cached data from Redis.
  - The cache expires after 24 hours.

### Example Request
```
curl --location 'http://localhost:8080/cep/81530300'
```

### Example Response

```json
{
    "cep": "81530-300",
    "logradouro": "Rua Almir Trova de Oliveira",
    "complemento": "",
    "unidade": "",
    "bairro": "Jardim das Américas",
    "localidade": "Curitiba",
    "uf": "PR",
    "estado": "Paraná",
    "regiao": "Sul",
    "ibge": "4106902",
    "gia": "",
    "ddd": "41",
    "siafi": "7535"
}
```


