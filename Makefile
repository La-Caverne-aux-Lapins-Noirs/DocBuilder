
SRC	=	$(shell find src/ -name "*.ph*")

all:
install:
		@./install.sh
$(SRC):
		@php -l $@
check:		$(SRC)
		@(cd tests/ && ./run.sh)
