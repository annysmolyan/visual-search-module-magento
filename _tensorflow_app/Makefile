init:  ## Run docker
	sudo sysctl -w vm.max_map_count=262144
	docker-compose up -d --build

stop: ## Stop docker
	docker-compose stop
	
start: ## Start docker
	docker-compose up -d
