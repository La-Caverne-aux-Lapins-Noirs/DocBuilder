
CHECK	=	$(shell find src/ -name "*.php" -or -name "*.phtml")

all:
install:
		./install.sh

$(CHECK):
		@php -l $@ > /dev/null
		@(cd tests/ && ./run.sh)
check:		$(CHECK)

.PHONY:		$(CHECK) check all install

