
CHECK	=	$(shell 'find src/ -name "*.ph*"')

all:
install:
		./install.sh

$(CHECK):
		php -l $@
check:		$(CHECK)

.PHONY:		$(CHECK) check all install

