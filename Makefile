
CHECK	=	$(shell find src/ -name "*.php" -or -name "*.phtml")

all:
install:
		./install.sh

$(CHECK):
		@php -l $@ > /dev/null
check:		$(CHECK)
		@(cd tests/ && ./run.sh)
		@./docbuilder \
		 -a tests/simple/activity.dab \
		 -i tests/simple/instance.dab \
		 -f PDFA5 \
		 -c tests/simple/configuration.dab \
		 -d tests/simple/dictionnary.dab \
		 -m tests/simple/medals/ \
		 -o output.pdf \
		 --keep-trace \
		 --engine html \
		 --language FR

.PHONY:		$(CHECK) check all install

