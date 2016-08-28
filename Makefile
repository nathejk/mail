NAME=mail
REPO=nathejk/$(NAME)
DOCKERHOST=172.17.0.1
DB_DSN=mysql://nathejk:3weekend@$(DOCKERHOST)/mail
MQ_DSN=nats://$(DOCKERHOST):4222
#SMTP_DSN=smtp://UN:PW@localhost
SMTP_DSN=tls://AKIAIBU3AB5YWG5VINIQ:Ap2p2sF2Sh6sc9ECwhjMAY76x9XDTxpiuwL7R0jSUVnI@email-smtp.us-east-1.amazonaws.com:25

build:
	docker build -t $(REPO) .

clean:
	rm -rf vendor

run:
	docker run -d -p 8005:80 --name $(NAME) --env DB_DSN=$(DB_DSN) --env MQ_DSN=$(MQ_DSN) --env SMTP_DSN=$(SMTP_DSN) -v `pwd`:/var/www -t $(REPO)

test:
	docker exec $(NAME) ./vendor/bin/phpunit ./src

stop:
	docker rm -f $(NAME)

rerun: stop run

.PHONY: clean run test build stop rerun
